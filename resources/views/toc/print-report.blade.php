@php
    /** @var array<string, mixed> $report */
    /** @var bool $autoprint */
    $statuses = $report['statuses'];
    $daily = $report['daily'];
    $dailyMax = (int) $report['daily_max'];
    $dailyCount = max(1, count($daily));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $report['title'] }}</title>
    <style>
        :root {
            --ika-green: #0b3d2e;
            --ika-green-mid: #0f5c40;
            --ika-gold: #f5c518;
            --ink: #0f172a;
            --muted: #334155;
            --line: #cbd5e1;
            --card: #ffffff;
            --bg: #f8faf9;
        }

        * { box-sizing: border-box; }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .sheet {
            width: 190mm;
            max-width: 100%;
            margin: 12px auto 24px;
            background: #fff;
            border: 1px solid var(--line);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .toolbar {
            width: 190mm;
            max-width: 100%;
            margin: 16px auto 0;
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
        }

        .toolbar button {
            border: 0;
            border-radius: 8px;
            padding: 10px 16px;
            font-weight: 700;
            cursor: pointer;
            background: var(--ika-green);
            color: #fff;
        }

        .toolbar a {
            color: var(--muted);
            font-size: 13px;
            text-decoration: none;
            margin-right: auto;
        }

        .header {
            background: linear-gradient(135deg, var(--ika-green) 0%, var(--ika-green-mid) 100%);
            color: #fff;
            padding: 18px 20px 14px;
            position: relative;
        }

        .header::after {
            content: "";
            display: block;
            height: 6px;
            background: var(--ika-gold);
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
        }

        .brand {
            font-size: 11px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ika-gold);
            font-weight: 700;
            margin: 0 0 4px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            line-height: 1.2;
            font-weight: 800;
        }

        .header .meta {
            margin-top: 8px;
            font-size: 12px;
            color: #ecfdf5;
        }

        .body {
            padding: 14px 16px 12px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }

        .card {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 10px 12px;
            background: var(--card);
            break-inside: avoid;
        }

        .card .label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--muted);
            font-weight: 700;
            margin: 0 0 4px;
        }

        .card .value {
            font-size: 24px;
            font-weight: 800;
            color: var(--ika-green);
            line-height: 1.1;
            margin: 0;
        }

        .card .sub {
            margin-top: 2px;
            font-size: 11px;
            color: var(--muted);
        }

        .status-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 12px;
        }

        .chip {
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: 700;
            color: var(--ink);
            background: #f1f5f9;
        }

        .section {
            margin-bottom: 10px;
            break-inside: avoid;
        }

        .section h2 {
            margin: 0 0 6px;
            font-size: 12px;
            font-weight: 800;
            color: var(--ika-green);
            border-bottom: 2px solid var(--ika-gold);
            padding-bottom: 3px;
            display: inline-block;
            min-width: 40%;
        }

        .chart-wrap {
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 8px 10px 4px;
            background: #fff;
        }

        .chart-wrap svg {
            width: 100%;
            height: 88px;
            display: block;
        }

        .chart-labels {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: var(--muted);
            margin-top: 2px;
        }

        .columns {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr 1fr;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th, td {
            border-bottom: 1px solid #e2e8f0;
            padding: 4px 2px;
            text-align: left;
            color: var(--ink);
            vertical-align: top;
        }

        th {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: var(--muted);
        }

        td.num, th.num {
            text-align: right;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .rank {
            color: var(--muted);
            width: 16px;
        }

        .footer {
            margin-top: 8px;
            padding: 10px 16px;
            background: var(--ika-green);
            color: var(--ika-gold);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            font-weight: 700;
        }

        .footer .link {
            color: #fff;
        }

        @media print {
            html, body {
                background: #fff !important;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                width: auto;
                margin: 0;
                border: 0;
                box-shadow: none;
            }
        }

        @media screen and (max-width: 800px) {
            .cards { grid-template-columns: repeat(2, 1fr); }
            .columns { grid-template-columns: 1fr; }
            .sheet, .toolbar { width: auto; margin-left: 12px; margin-right: 12px; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ url('/admin/toc-dashboard') }}">← Kembali ke dashboard</a>
        <button type="button" onclick="window.print()">Cetak / simpan PDF</button>
    </div>

    <main class="sheet">
        <header class="header">
            <p class="brand">IKA UNIMED</p>
            <h1>{{ $report['title'] }}</h1>
            <p class="meta">{{ $report['generated_at_label'] }}</p>
        </header>

        <div class="body">
            <section class="cards" aria-label="Ringkasan angka">
                <article class="card">
                    <p class="label">Total pendaftar</p>
                    <p class="value">{{ $report['total_label'] }}</p>
                </article>
                <article class="card">
                    <p class="label">Hari ini</p>
                    <p class="value">{{ $report['today_label'] }}</p>
                </article>
                <article class="card">
                    <p class="label">Alumni UNIMED</p>
                    <p class="value">{{ $report['alumni_label'] }}</p>
                    <p class="sub">{{ $report['alumni_percentage_label'] }} dari total</p>
                </article>
                <article class="card">
                    <p class="label">Sisa waktu</p>
                    <p class="value" style="font-size:18px">{{ $report['remaining_label'] }}</p>
                    <p class="sub">Tutup {{ $report['closes_at_label'] }}</p>
                </article>
            </section>

            <section class="status-bar" aria-label="Status">
                <span class="chip">Baru {{ $statuses['baru'] ?? 0 }}</span>
                <span class="chip">Terverifikasi {{ $statuses['terverifikasi'] ?? 0 }}</span>
                <span class="chip">Lolos {{ $statuses['lolos'] ?? 0 }}</span>
                <span class="chip">Ditolak {{ $statuses['ditolak'] ?? 0 }}</span>
            </section>

            <section class="section">
                <h2>Tren harian (WIB)</h2>
                <div class="chart-wrap">
                    @if ($daily === [])
                        <p style="font-size:12px;color:var(--muted);margin:8px 0">Belum ada data.</p>
                    @else
                        @php
                            $width = 600;
                            $height = 90;
                            $pad = 6;
                            $values = array_values($daily);
                            $labels = array_keys($daily);
                            $n = count($values);
                            $points = [];
                            for ($i = 0; $i < $n; $i++) {
                                $x = $n === 1
                                    ? $width / 2
                                    : $pad + ($i / ($n - 1)) * ($width - 2 * $pad);
                                $y = $height - $pad - (((int) $values[$i] / $dailyMax) * ($height - 2 * $pad));
                                $points[] = round($x, 1).','.round($y, 1);
                            }
                            $polyline = implode(' ', $points);
                        @endphp
                        <svg viewBox="0 0 {{ $width }} {{ $height }}" role="img" aria-label="Grafik tren harian">
                            <line x1="0" y1="{{ $height - 4 }}" x2="{{ $width }}" y2="{{ $height - 4 }}" stroke="#e2e8f0" stroke-width="1" />
                            <polyline fill="none" stroke="#0f5c40" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="{{ $polyline }}" />
                            @foreach ($points as $point)
                                @php [$px, $py] = explode(',', $point); @endphp
                                <circle cx="{{ $px }}" cy="{{ $py }}" r="3.5" fill="#f5c518" stroke="#0b3d2e" stroke-width="1.5" />
                            @endforeach
                        </svg>
                        <div class="chart-labels">
                            <span>{{ $labels[0] ?? '' }}</span>
                            <span>{{ $labels[(int) floor(($n - 1) / 2)] ?? '' }}</span>
                            <span>{{ $labels[$n - 1] ?? '' }}</span>
                        </div>
                    @endif
                </div>
            </section>

            <section class="columns">
                <div class="section">
                    <h2>Top 5 Provinsi</h2>
                    <table>
                        <thead>
                            <tr><th class="rank">#</th><th>Provinsi</th><th class="num">Jumlah</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($report['provinces'] as $i => $row)
                                <tr>
                                    <td class="rank">{{ $i + 1 }}</td>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="num">{{ $row['total'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="section">
                    <h2>Jenjang</h2>
                    <table>
                        <thead>
                            <tr><th>Jenjang</th><th class="num">Jumlah</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($report['jenjang'] as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="num">{{ $row['total'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="section">
                    <h2>Top 5 Bidang Studi</h2>
                    <table>
                        <thead>
                            <tr><th class="rank">#</th><th>Bidang</th><th class="num">Jumlah</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($report['bidang_studi'] as $i => $row)
                                <tr>
                                    <td class="rank">{{ $i + 1 }}</td>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="num">{{ $row['total'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <footer class="footer">
            <span>Tanpa data pribadi · Tutup {{ $report['closes_at_label'] }}</span>
            <span class="link">{{ $report['link'] }}</span>
        </footer>
    </main>

    @if ($autoprint)
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 250);
            });
        </script>
    @endif
</body>
</html>
