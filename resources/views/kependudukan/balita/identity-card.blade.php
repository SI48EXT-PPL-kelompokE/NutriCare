<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu Identitas Balita | NutriCare</title>
    <style>
        :root {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: #0f172a;
            background: #f8fafc;
        }
        body {
            margin: 0;
            padding: 1rem;
        }
        main {
            max-width: 90mm;
            margin: 2rem auto;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1rem;
        }
        .actions a, .actions button {
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            background: white;
            color: #005953;
            padding: .55rem .75rem;
            font: inherit;
            font-size: .875rem;
            font-weight: 600;
            text-decoration: none;
        }
        .actions button {
            border-color: #005953;
            background: #005953;
            color: white;
            cursor: pointer;
        }
        .card {
            box-sizing: border-box;
            min-height: 55mm;
            padding: 4mm;
            border: .25mm solid #cbd5e1;
            border-top: 1mm solid #005953;
            border-radius: 2mm;
            background: white;
        }
        .card header {
            border-bottom: .25mm solid #e2e8f0;
            padding-bottom: 2mm;
        }
        .brand {
            margin: 0;
            color: #005953;
            font-size: 9pt;
            font-weight: 800;
        }
        h1 {
            margin: .5mm 0 0;
            font-size: 12pt;
        }
        .identity {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(24mm, 34mm);
            align-items: center;
            gap: 2mm;
            padding-top: 2mm;
        }
        dl, dd, figure {
            margin: 0;
        }
        dl {
            display: grid;
            gap: 1.5mm;
        }
        dt {
            color: #475569;
            font-size: 8pt;
        }
        dd {
            font-size: 9pt;
            font-weight: 600;
            overflow-wrap: anywhere;
        }
        figure {
            text-align: center;
        }
        figure img {
            display: block;
            width: 27mm;
            max-width: 100%;
            height: auto;
            margin: 0 auto;
        }
        figcaption {
            font: 8pt ui-monospace, monospace;
            overflow-wrap: anywhere;
        }
        #print-error {
            color: #9f1239;
            font-size: .875rem;
        }
        @page {
            margin: 12mm;
        }
        @media print {
            :root, body {
                background: white;
                padding: 0;
            }
            main {
                width: 90mm;
                margin: 0;
            }
            .actions, #print-error {
                display: none;
            }
            .card {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <main>
        <div class="actions">
            <a href="{{ route('kependudukan.balita.index') }}">Kembali ke Daftar Balita</a>
            <button id="print-card" type="button" @if($printOnLoad) data-print-on-load @endif>Cetak Kartu</button>
        </div>
        <p id="print-error" role="status" hidden>QR Code gagal dimuat. Muat ulang kartu sebelum mencetak.</p>

        <article class="card">
            <header>
                <p class="brand">NutriCare</p>
                <h1>Kartu Identitas Balita</h1>
            </header>

            <div class="identity">
                <dl>
                    <div>
                        <dt>Nama Lengkap</dt>
                        <dd>{{ $balita->name }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal Lahir</dt>
                        <dd><time datetime="{{ $balita->birth_date->toDateString() }}">{{ $balita->birth_date->locale('id')->translatedFormat('j F Y') }}</time></dd>
                    </div>
                    <div>
                        <dt>Jenis Kelamin</dt>
                        <dd>{{ $balita->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd>
                    </div>
                </dl>
                <figure>
                    <img id="card-qr" src="{{ $qrImage }}" width="232" height="232" alt="QR Identitas Balita">
                    <figcaption>{{ $balita->qr_code }}</figcaption>
                </figure>
            </div>
        </article>
    </main>
    <script>
        const printButton = document.getElementById('print-card');
        const cardQr = document.getElementById('card-qr');

        async function printIdentityCard() {
            try {
                await cardQr.decode();
            } catch {
                document.getElementById('print-error').hidden = false;
                return;
            }

            window.print();
        }

        printButton.addEventListener('click', printIdentityCard);
        if (printButton.hasAttribute('data-print-on-load')) {
            window.addEventListener('load', printIdentityCard, { once: true });
        }
    </script>
</body>
</html>
