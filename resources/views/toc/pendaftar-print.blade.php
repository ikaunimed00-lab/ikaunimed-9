@php
    /** @var \App\Models\PendaftarTeacherOfChampions $pendaftar */
    /** @var bool $canViewContact */
    /** @var bool $autoprint */
    $whatsapp = $canViewContact ? $pendaftar->whatsapp : \App\Models\PendaftarTeacherOfChampions::maskContact($pendaftar->whatsapp);
    $email = $canViewContact ? $pendaftar->email : \App\Models\PendaftarTeacherOfChampions::maskContact($pendaftar->email);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pendaftar {{ $pendaftar->nomor_pendaftaran }}</title>
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
        @page { size: A4 portrait; margin: 10mm; }
        html, body {
            margin: 0; padding: 0; background: #f8faf9; color: var(--ink);
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
        }
        .toolbar {
            width: 190mm; max-width: 100%; margin: 10px auto 0; display: flex; gap: 8px;
            align-items: center; justify-content: flex-end;
        }
        .toolbar button {
            border: 0; border-radius: 8px; padding: 8px 14px; font-weight: 700; cursor: pointer;
            background: var(--ika-green); color: #fff;
        }
        .toolbar a { color: var(--muted); font-size: 12px; text-decoration: none; margin-right: auto; }
        .sheet {
            width: 190mm; max-width: 100%; margin: 8px auto 16px; background: #fff;
            border: 1px solid var(--line); box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08); overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, var(--ika-green) 0%, var(--ika-green-mid) 100%);
            color: #fff; padding: 14px 16px 12px; position: relative;
        }
        .header::after {
            content: ""; display: block; height: 5px; background: var(--ika-gold);
            position: absolute; left: 0; right: 0; bottom: 0;
        }
        .brand { font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ika-gold); font-weight: 700; margin: 0 0 2px; }
        .header h1 { margin: 0; font-size: 18px; line-height: 1.2; font-weight: 800; }
        .header .meta { margin-top: 4px; font-size: 11px; color: #ecfdf5; }
        .body { padding: 12px 14px 10px; }
        .nomor {
            margin: 0 0 10px; padding: 10px 12px; border-radius: 10px; background: #f1f5f9;
            display: flex; justify-content: space-between; gap: 12px; align-items: baseline;
        }
        .nomor strong { font-family: ui-monospace, Consolas, monospace; font-size: 18px; color: var(--ika-green); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 14px; }
        .field .label { margin: 0; font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted); font-weight: 700; }
        .field .value { margin: 1px 0 0; font-size: 12px; font-weight: 650; color: var(--ink); word-break: break-word; }
        .section { margin-top: 10px; }
        .section h2 {
            margin: 0 0 6px; font-size: 11px; font-weight: 800; color: var(--ika-green);
            border-bottom: 2px solid var(--ika-gold); padding-bottom: 2px; display: inline-block; min-width: 30%;
        }
        .footer {
            margin-top: 10px; padding-top: 8px; border-top: 1px solid var(--line);
            font-size: 10px; color: var(--muted); display: flex; justify-content: space-between;
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
        <a href="{{ url('/admin/pendaftar-teacher-of-champions/'.$pendaftar->id) }}">← Kembali ke detail</a>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>
    <div class="sheet">
        <div class="header">
            <p class="brand">IKA UNIMED · Admin ToC</p>
            <h1>Detail Pendaftar Teacher of Champions</h1>
            <p class="meta">Dicetak {{ now()->timezone(\App\Models\Kegiatan::TIMEZONE)->format('d M Y, H:i') }} WIB</p>
        </div>
        <div class="body">
            <div class="nomor">
                <span>Nomor pendaftaran</span>
                <strong>{{ $pendaftar->nomor_pendaftaran }}</strong>
            </div>

            <div class="section">
                <h2>Identitas</h2>
                <div class="grid">
                    <div class="field"><p class="label">Nama</p><p class="value">{{ $pendaftar->nama }}</p></div>
                    <div class="field"><p class="label">Status</p><p class="value">{{ $pendaftar->statusLabel() }}</p></div>
                    <div class="field"><p class="label">WhatsApp</p><p class="value">{{ $whatsapp }}</p></div>
                    <div class="field"><p class="label">Email</p><p class="value">{{ $email }}</p></div>
                    <div class="field"><p class="label">Instagram</p><p class="value">{{ $pendaftar->instagram ?: '-' }}</p></div>
                    <div class="field"><p class="label">Tanggal daftar</p><p class="value">{{ $pendaftar->registeredAtWibLabel() }}</p></div>
                </div>
            </div>

            <div class="section">
                <h2>Instansi & wilayah</h2>
                <div class="grid">
                    <div class="field"><p class="label">Asal instansi</p><p class="value">{{ $pendaftar->asal_instansi }}</p></div>
                    <div class="field"><p class="label">Jenjang</p><p class="value">{{ $pendaftar->jenjangLabel() }}</p></div>
                    <div class="field"><p class="label">Provinsi</p><p class="value">{{ $pendaftar->provinsi }}</p></div>
                    <div class="field"><p class="label">Kabupaten/kota</p><p class="value">{{ $pendaftar->kabupaten_kota }}</p></div>
                    <div class="field"><p class="label">Bidang studi</p><p class="value">{{ $pendaftar->bidang_studi }}</p></div>
                    <div class="field"><p class="label">Status kepegawaian</p><p class="value">{{ $pendaftar->status_kepegawaian ?: '-' }}</p></div>
                    <div class="field"><p class="label">Lama mengabdi</p><p class="value">{{ $pendaftar->lama_mengabdi_tahun }} tahun</p></div>
                    <div class="field"><p class="label">Asal universitas</p><p class="value">{{ $pendaftar->asal_universitas }}</p></div>
                    <div class="field"><p class="label">Alumni UNIMED</p><p class="value">{{ $pendaftar->alumni_unimed ? 'Ya' : 'Tidak' }}</p></div>
                </div>
            </div>

            <div class="section">
                <h2>Catatan</h2>
                <div class="grid">
                    <div class="field"><p class="label">Catatan panitia</p><p class="value">{{ $pendaftar->catatan ?: '-' }}</p></div>
                    <div class="field"><p class="label">Catatan internal</p><p class="value">{{ $pendaftar->catatan_internal ?: '-' }}</p></div>
                </div>
            </div>

            <div class="footer">
                <span>ikaunimed.or.id/toc</span>
                <span>ID #{{ $pendaftar->id }}</span>
            </div>
        </div>
    </div>
    @if ($autoprint)
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>
