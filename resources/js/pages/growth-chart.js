// Grafik pertumbuhan balita (KF-16).
// Data dibaca dari atribut data-chart pada kontainer (bukan AJAX).
import { Chart } from '../charts/register';

const ANGKA = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });
const ANGKA_Z = (z) => (z >= 0 ? '+' : '-') + Math.abs(z).toFixed(2).replace('.', ',');

// Ambil warna dari token tema (CSS variable); fallback bila tidak tersedia.
function warna(nama, fallback) {
    const nilai = getComputedStyle(document.documentElement).getPropertyValue(nama).trim();
    return nilai || fallback;
}

const DASH = {
    rujukan: [6, 4],
    waspada: [5, 4],
    batasAtas: [3, 4],
};

function labelSumbuX(kode) {
    return kode === 'bb_tb' ? 'Tinggi/Panjang badan (cm)' : 'Usia (bulan)';
}

function labelSumbuY(kode, satuan) {
    return kode === 'tb_u' ? 'Tinggi/Panjang badan (cm)' : `Berat badan (${satuan})`;
}

// Susun dataset kurva acuan + titik hasil ukur untuk satu indikator.
function datasetIndikator(indikator, palet) {
    const datasets = [];

    if (Array.isArray(indikator.kurva)) {
        indikator.kurva.forEach((garis, index) => {
            // Gaya garis berbeda per SD (variasi pola, bukan hanya warna).
            const gaya = {
                '-3': { warna: palet.rujukan, dash: DASH.rujukan, lebar: 1.5 },
                '-2': { warna: palet.waspada, dash: DASH.waspada, lebar: 1.5 },
                '0': { warna: palet.median, dash: [], lebar: 2 },
                '2': { warna: palet.batasAtas, dash: DASH.batasAtas, lebar: 1.5 },
            }[String(garis.k)] || { warna: palet.median, dash: [], lebar: 1.5 };

            datasets.push({
                label: garis.label,
                data: garis.data,
                borderColor: gaya.warna,
                borderWidth: gaya.lebar,
                borderDash: gaya.dash,
                pointRadius: 0,
                pointHitRadius: 0,
                fill: false,
                // Arsiran zona normal antara -2 SD dan +2 SD.
                ...(garis.k === -2 ? { fill: { target: 3 }, backgroundColor: palet.zona } : {}),
                _kurva: true,
                _index: index,
            });
        });
    }

    datasets.push({
        label: 'Hasil ukur',
        data: indikator.titik.map((t) => ({
            x: t.x,
            y: t.y,
            tanggal: t.tanggal,
            usia_bln: t.usia_bulan,
            z: t.z,
            status: t.status,
            terbaru: t.terbaru,
        })),
        borderColor: palet.garis,
        backgroundColor: palet.garis,
        borderWidth: 3,
        tension: 0.25,
        fill: false,
        pointBackgroundColor: '#ffffff',
        pointBorderColor: palet.garis,
        pointBorderWidth: 2,
        pointRadius: (ctx) => (ctx.raw && ctx.raw.terbaru ? 6 : 4),
        pointHoverRadius: (ctx) => (ctx.raw && ctx.raw.terbaru ? 8 : 6),
        _kurva: false,
    });

    return datasets;
}

function opsi(indikator, palet, reduceMotion) {
    return {
        parsing: false,
        responsive: true,
        maintainAspectRatio: false,
        animation: reduceMotion ? false : { duration: 300 },
        interaction: { mode: 'nearest', intersect: true },
        layout: { padding: { top: 8, right: 8 } },
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    boxWidth: 10,
                    boxHeight: 10,
                    padding: 12,
                    color: palet.teks,
                    font: { size: 11 },
                    // Sembunyikan "Hasil ukur" dari legenda kurva? Tetap tampilkan.
                },
            },
            tooltip: {
                callbacks: {
                    title: (items) => {
                        const raw = items[0] && items[0].raw;
                        if (raw && raw.usia_bln !== undefined && raw.usia_bln !== null) {
                            return `Usia ${raw.usia_bln} bln (${raw.tanggal})`;
                        }
                        return items[0] ? items[0].dataset.label : '';
                    },
                    label: (item) => {
                        const raw = item.raw || {};
                        if (raw.z === undefined && raw.status === undefined) {
                            return item.dataset.label;
                        }
                        const baris = [
                            `${indikator.label_ukuran}: ${ANGKA.format(raw.y)} ${indikator.satuan_y}`,
                            `Z-score: ${raw.z !== null && raw.z !== undefined ? ANGKA_Z(raw.z) + ' SD' : 'belum tersedia'}`,
                            `Status: ${raw.status || 'belum dapat dinilai'}`,
                        ];
                        if (raw.terbaru) {
                            baris.push('Terbaru');
                        }
                        return baris;
                    },
                },
            },
        },
        scales: {
            x: {
                type: 'linear',
                min: indikator.rentang_x[0],
                max: indikator.rentang_x[1],
                grid: { color: palet.grid },
                ticks: { color: palet.teks, font: { size: 10 }, maxRotation: 0 },
                title: {
                    display: true,
                    text: labelSumbuX(indikator.kode),
                    color: palet.teks,
                    font: { size: 11 },
                },
            },
            y: {
                beginAtZero: false,
                grid: { color: palet.grid },
                ticks: { color: palet.teks, font: { size: 10 } },
                title: {
                    display: true,
                    text: labelSumbuY(indikator.kode, indikator.satuan_y),
                    color: palet.teks,
                    font: { size: 11 },
                },
            },
        },
    };
}

function inisialisasi(kontainer) {
    const kanvas = kontainer.querySelector('canvas');
    if (!kanvas) {
        return;
    }

    let data;
    try {
        data = JSON.parse(kontainer.dataset.chart || '{}');
    } catch (e) {
        return; // Biarkan fallback tampil.
    }

    const indikator = data.indikator || {};
    if (!indikator.bb_u) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const palet = {
        garis: warna('--color-primary-700', '#0f766e'),
        median: warna('--color-neutral-400', '#94a3b8'),
        waspada: warna('--color-warning-500', '#f59e0b'),
        rujukan: warna('--color-danger-600', '#dc2626'),
        batasAtas: warna('--color-primary-400', '#2dd4bf'),
        zona: warna('--color-primary-50', '#f0fdfa'),
        grid: warna('--color-neutral-200', '#e2e8f0'),
        teks: warna('--color-neutral-500', '#64748b'),
    };

    let chart = null;
    let aktif = 'bb_u';

    const gambar = (kode) => {
        aktif = kode;
        const ind = indikator[kode];

        if (chart) {
            chart.destroy();
            chart = null;
        }

        chart = new Chart(kanvas.getContext('2d'), {
            type: 'line',
            data: { datasets: datasetIndikator(ind, palet) },
            options: opsi(ind, palet, reduceMotion),
        });

        kanvas.setAttribute('aria-label', `Grafik ${ind.label}, ${ind.jumlah_titik} pengukuran`);
    };

    gambar(aktif);

    // Ekspose untuk tab Alpine dan pengujian (Dusk).
    window.growthChart = { pilih: gambar, instance: () => chart };

    // Tampilkan kanvas, sembunyikan fallback.
    const bungkus = kontainer.querySelector('[data-chart-canvas-wrap]');
    const fallback = kontainer.querySelector('[data-chart-fallback]');
    if (bungkus) bungkus.classList.remove('hidden');
    if (fallback) fallback.classList.add('hidden');

    window.addEventListener('beforeunload', () => {
        if (chart) {
            chart.destroy();
            chart = null;
        }
    });
}

function mulai() {
    document.querySelectorAll('[data-growth-chart]').forEach(inisialisasi);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mulai);
} else {
    mulai();
}
