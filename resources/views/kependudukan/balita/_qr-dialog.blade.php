<dialog
    id="balita-qr-dialog"
    aria-labelledby="balita-qr-title"
    class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-slate-200 bg-white p-0 text-slate-800 shadow-2xl backdrop:bg-slate-900/50"
>
    <div class="p-5 sm:p-6">
        <h3 id="balita-qr-title" class="text-lg font-bold text-slate-900">QR Identitas Balita</h3>
        <p id="balita-qr-name" class="mt-1 break-words text-sm font-semibold text-slate-700"></p>

        <div class="my-4 flex min-h-48 items-center justify-center rounded-xl border border-slate-200 bg-white p-2">
            <p id="balita-qr-status" role="status" class="text-center text-sm text-slate-600"></p>
            <div id="balita-qr-image"></div>
        </div>
        <p id="balita-qr-code" class="break-all text-center font-mono text-sm font-semibold text-slate-700"></p>

        <form method="dialog" class="mt-5 flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 pt-4 text-xs font-semibold">
            <button autofocus class="rounded-lg px-3 py-2 text-slate-700 hover:bg-slate-100">Tutup</button>
            <a id="balita-card-link" class="rounded-lg border border-slate-200 px-3 py-2 hover:bg-slate-50">Lihat Kartu Identitas</a>
            <a id="balita-print-link" class="rounded-lg bg-teal-600 px-3 py-2 text-white hover:bg-teal-700">Cetak Kartu</a>
        </form>
    </div>
</dialog>

@push('scripts')
<script>
    (() => {
        const dialog = document.getElementById('balita-qr-dialog');
        const imageContainer = document.getElementById('balita-qr-image');
        const status = document.getElementById('balita-qr-status');
        const cardLink = document.getElementById('balita-card-link');
        const printLink = document.getElementById('balita-print-link');

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-identity-qr]');
            if (!button) return;

            const { identityQr, identityCard, identityName, identityCode } = button.dataset;
            document.getElementById('balita-qr-name').textContent = identityName;
            document.getElementById('balita-qr-code').textContent = identityCode;
            cardLink.href = identityCard;

            const printUrl = new URL(identityCard, window.location.href);
            printUrl.searchParams.set('print', '1');
            printLink.href = printUrl;

            status.hidden = false;
            status.textContent = 'Memuat QR Code…';

            const image = new Image(176, 176);
            image.hidden = true;
            image.alt = 'QR Identitas Balita';
            imageContainer.replaceChildren(image);
            image.addEventListener('load', () => {
                if (!dialog.open || !image.isConnected) return;
                status.hidden = true;
                image.hidden = false;
            }, { once: true });
            image.addEventListener('error', () => {
                if (!dialog.open || !image.isConnected) return;
                status.textContent = 'QR Code gagal dimuat. Tutup lalu buka kembali untuk mencoba lagi.';
            }, { once: true });

            dialog.showModal();
            image.src = identityQr;
        });
    })();
</script>
@endpush
