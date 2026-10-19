@php
    /** @var \App\Models\PendaftarTeacherOfChampions $pendaftar */
    /** @var bool $autoprint */
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Pendaftaran {{ $pendaftar->nomor_pendaftaran }} — Teacher of Champions</title>
    <style>
        :root {
            --ika-green: #0b3d2e;
            --ika-green-mid: #0f5c40;
            --ika-gold: #f5c518;
            --ink: #0f172a;
            --muted: #334155;
            --line: #cbd5e1;
        }
        * { box-sizing: border-box; }
        @page { size: A4 portrait; margin: 12mm; }
        html, body {
            margin: 0; padding: 0; background: #f8faf9; color: var(--ink);
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
        }
        .toolbar {
            width: 186mm; max-width: 100%; margin: 12px auto 0; display: flex; gap: 8px;
            align-items: center; justify-content: flex-end;
        }
        .toolbar button {
            border: 0; border-radius: 8px; padding: 8px 14px; font-weight: 700; cursor: pointer;
            background: var(--ika-green); color: #fff;
        }
        .toolbar a { color: var(--muted); font-size: 12px; text-decoration: none; margin-right: auto; }
        .sheet {
            width: 186mm; max-width: 100%; margin: 8px auto 16px; background: #fff;
            border: 1px solid var(--line); box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08); overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, var(--ika-green) 0%, var(--ika-green-mid) 100%);
            color: #fff; padding: 18px 20px 16px; position: relative;
        }
        .header::after {
            content: ""; display: block; height: 5px; background: var(--ika-gold);
            position: absolute; left: 0; right: 0; bottom: 0;
        }
        .brand { font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ika-gold); font-weight: 700; margin: 0 0 4px; }
        .header h1 { margin: 0; font-size: 22px; line-height: 1.25; font-weight: 800; }
        .header .meta { margin-top: 6px; font-size: 12px; color: #ecfdf5; }
        .body { padding: 22px 24px 20px; }
        .nomor-box {
            margin: 0 0 18px; padding: 16px 18px; border-radius: 12px;
            background: var(--ika-green); color: var(--ika-gold); text-align: center;
        }
        .nomor-box .label { margin: 0 0 6px; font-size: 11px; letter-spacing: 0.06em; text-transform: uppercase; color: #ecfdf5; font-weight: 700; }
        .nomor-box .value { margin: 0; font-family: ui-monospace, Consolas, monospace; font-size: 28px; font-weight: 800; letter-spacing: 0.08em; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 18px; }
        .field .label { margin: 0 0 2px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted); font-weight: 700; }
        .field .value { margin: 0; font-size: 14px; font-weight: 700; color: var(--ika-green); }
        .note {
            margin-top: 20px; padding: 12px 14px; border-radius: 10px; background: #fff8db;
            border: 1px solid #f5c518; color: var(--ink); font-size: 13px; font-weight: 600;
        }
        .footer {
            margin-top: 18px; padding-top: 12px; border-top: 1px solid var(--line);
            font-size: 11px; color: var(--muted); display: flex; justify-content: space-between; gap: 12px;
        }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { margin: 0; width: auto; border: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ url('/toc/cek-status') }}">← Cek Status</a>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <div class="sheet">
        <div class="header">
            <p class="brand">IKA UNIMED</p>
            <h1>Bukti Pendaftaran Teacher of Champions</h1>
            <p class="meta">Simpan nomor ini sebagai bukti pendaftaran Anda.</p>
        </div>
        <div class="body">
            <div class="nomor-box">
                <p class="label">Nomor pendaftaran</p>
                <p class="value">{{ $pendaftar->nomor_pendaftaran }}</p>
            </div>
            <div class="grid">
                <div class="field">
                    <p class="label">Nama</p>
                    <p class="value">{{ $pendaftar->nama }}</p>
                </div>
                <div class="field">
                    <p class="label">Jenjang</p>
                    <p class="value">{{ $pendaftar->jenjangLabel() }}</p>
                </div>
                <div class="field">
                    <p class="label">Bidang studi</p>
                    <p class="value">{{ $pendaftar->bidang_studi ?: '-' }}</p>
                </div>
                <div class="field">
                    <p class="label">Tanggal daftar</p>
                    <p class="value">{{ $pendaftar->registeredAtWibLabel() }}</p>
                </div>
            </div>
            <p class="note">Simpan nomor pendaftaran ini. Anda dapat mengeceknya kembali di halaman Cek Status.</p>
            <div class="footer">
                <span>ikaunimed.or.id/toc</span>
                <span>Teacher of Champions 2026</span>
            </div>
        </div>
    </div>
    @if ($autoprint)
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>
