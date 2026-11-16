@php
    /** @var array<string, mixed> $report */
    /** @var bool $autoprint */
    $statuses = $report['statuses'];
    $daily = $report['daily'];
    $dailyMax = (int) $report['daily_max'];
    $dailyLast7 = $report['daily_last7'] ?? [];
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
        @page { size: A4 portrait; margin: 8mm; }
        html, body {
            margin: 0; padding: 0; background: var(--bg); color: var(--ink);
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
        }
        .sheet {
            width: 194mm; max-width: 100%; margin: 8px auto 16px; background: #fff;
            border: 1px solid var(--line); box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08); overflow: hidden;
        }
        .toolbar {
            width: 194mm; max-width: 100%; margin: 10px auto 0; display: flex; gap: 8px;
            align-items: center; justify-content: flex-end;
        }
        .toolbar button {
            border: 0; border-radius: 8px; padding: 8px 14px; font-weight: 700; cursor: pointer;
            background: var(--ika-green); color: #fff;
        }
        .toolbar a { color: var(--muted); font-size: 12px; text-decoration: none; margin-right: auto; }
        .header {
            background: linear-gradient(135deg, var(--ika-green) 0%, var(--ika-green-mid) 100%);
            color: #fff; padding: 12px 14px 10px; position: relative;
        }
        .header::after {
            content: ""; display: block; height: 5px; background: var(--ika-gold);
            position: absolute; left: 0; right: 0; bottom: 0;
        }
        .brand { font-size: 10px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ika-gold); font-weight: 700; margin: 0 0 2px; }
        .header h1 { margin: 0; font-size: 18px; line-height: 1.2; font-weight: 800; }
        .header .meta { margin-top: 4px; font-size: 11px; color: #ecfdf5; }
        .body { padding: 10px 12px 8px; }
        .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-bottom: 8px; }
        .card { border: 1px solid var(--line); border-radius: 8px; padding: 7px 9px; background: var(--card); break-inside: avoid; }
        .card .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--muted); font-weight: 700; margin: 0 0 2px; }
        .card .value { font-size: 20px; font-weight: 800; color: var(--ika-green); line-height: 1.1; margin: 0; }
        .card .sub { margin-top: 1px; font-size: 10px; color: var(--muted); }
        .status-bar { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 8px; }
        .chip { border: 1px solid var(--line); border-radius: 999px; padding: 3px 8px; font-size: 10px; font-weight: 700; color: var(--ink); background: #f1f5f9; }
        .section { margin-bottom: 7px; break-inside: avoid; }
        .section h2 {
            margin: 0 0 4px; font-size: 11px; font-weight: 800; color: var(--ika-green);
            border-bottom: 2px solid var(--ika-gold); padding-bottom: 2px; display: inline-block; min-width: 35%;
        }
        .trend-row { display: grid; grid-template-columns: 1.35fr 0.85fr; gap: 8px; margin-bottom: 8px; }
        .chart-wrap { border: 1px solid var(--line); border-radius: 8px; padding: 4px 6px 2px; background: #fff; }
        .chart-wrap svg { width: 100%; height: 118px; display: block; }
        .columns-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
        .columns-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 2px 2px; text-align: left; color: var(--ink); vertical-align: top; }
        th { font-size: 9px; text-transform: uppercase; letter-spacing: 0.03em; color: var(--muted); }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .rank { color: var(--muted); width: 14px; }
        .footer {
            margin-top: 4px; padding: 7px 12px; background: var(--ika-green); color: var(--ika-gold);
            display: flex; justify-content: space-between; align-items: center; font-size: 10px; font-weight: 700;
        }
        .footer .link { color: #fff; }
        @media print {
            html, body { background: #fff !important; }
            .toolbar { display: none !important; }
            .sheet { width: auto; margin: 0; border: 0; box-shadow: none; }
        }
        @media screen and (max-width: 800px) {
            .cards { grid-template-columns: repeat(2, 1fr); }
            .trend-row, .columns-3, .columns-2 { grid-template-columns: 1fr; }
            .sheet, .toolbar { width: auto; margin-left: 10px; margin-right: 10px; }
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
                    <p class="value" style="font-size:15px">{{ $report['remaining_label'] }}</p>
                    <p class="sub">Tutup {{ $report['closes_at_label'] }}</p>
                </article>
            </section>

            <section class="status-bar" aria-label="Status">
                <span class="chip">Baru {{ $statuses['baru'] ?? 0 }}</span>
                <span class="chip">Terverifikasi {{ $statuses['terverifikasi'] ?? 0 }}</span>
                <span class="chip">Lolos {{ $statuses['lolos'] ?? 0 }}</span>
                <span class="chip">Ditolak {{ $statuses['ditolak'] ?? 0 }}</span>
            </section>

            <section class="trend-row">
                <div class="section">
                    <h2>Tren harian (WIB)</h2>
                    <div class="chart-wrap">
                        @if ($daily === [])
                            <p style="font-size:11px;color:var(--muted);margin:6px 0">Belum ada data.</p>
                        @else
                            @php
                                $width = 640;
                                $height = 130;
                                $padL = 10;
                                $padR = 10;
                                $padT = 18;
                                $padB = 28;
                                $values = array_values($daily);
                                $labels = array_keys($daily);
                                $n = count($values);
                                $points = [];
                                for ($i = 0; $i < $n; $i++) {
                                    $x = $n === 1
                                        ? $width / 2
                                        : $padL + ($i / ($n - 1)) * ($width - $padL - $padR);
                                    $y = $height - $padB - (((int) $values[$i] / $dailyMax) * ($height - $padT - $padB));
                                    $points[] = ['x' => round($x, 1), 'y' => round($y, 1), 'v' => (int) $values[$i], 'd' => $labels[$i]];
                                }
                                $polyline = collect($points)->map(fn ($p) => $p['x'].','.$p['y'])->implode(' ');
                            @endphp
                            <svg viewBox="0 0 {{ $width }} {{ $height }}" role="img" aria-label="Grafik tren harian">
                                <line x1="0" y1="{{ $height - $padB + 4 }}" x2="{{ $width }}" y2="{{ $height - $padB + 4 }}" stroke="#e2e8f0" stroke-width="1" />
                                <polyline fill="none" stroke="#0f5c40" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $polyline }}" />
                                @foreach ($points as $point)
                                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#f5c518" stroke="#0b3d2e" stroke-width="1.2" />
                                    <text x="{{ $point['x'] }}" y="{{ max(10, $point['y'] - 7) }}" text-anchor="middle" font-size="9" font-weight="700" fill="#0b3d2e">{{ $point['v'] }}</text>
                                    <text x="{{ $point['x'] }}" y="{{ $height - 8 }}" text-anchor="middle" font-size="8" fill="#334155">{{ \Carbon\CarbonImmutable::parse($point['d'])->format('d/m') }}</text>
                                @endforeach
                            </svg>
                        @endif
                    </div>
                </div>

                <div class="section">
                    <h2>Pendaftar per hari (7 hari)</h2>
                    <table>
                        <thead>
                            <tr><th>Tanggal</th><th class="num">Pendaftar</th><th class="num">Kumulatif</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($dailyLast7 as $row)
                                <tr>
                                    <td>{{ $row['date'] }}</td>
                                    <td class="num">{{ $row['count'] }}</td>
                                    <td class="num">{{ $row['cumulative'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="columns-3">
                <div class="section">
                    <h2>Top 5 Provinsi</h2>
                    <table>
                        <thead><tr><th class="rank">#</th><th>Provinsi</th><th class="num">Jml</th></tr></thead>
                        <tbody>
                            @forelse ($report['provinces'] as $i => $row)
                                <tr><td class="rank">{{ $i + 1 }}</td><td>{{ $row['label'] }}</td><td class="num">{{ $row['total'] }}</td></tr>
                            @empty
                                <tr><td colspan="3">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="section">
                    <h2>Jenjang</h2>
                    <table>
                        <thead><tr><th>Jenjang</th><th class="num">Jml</th></tr></thead>
                        <tbody>
                            @forelse ($report['jenjang'] as $row)
                                <tr><td>{{ $row['label'] }}</td><td class="num">{{ $row['total'] }}</td></tr>
                            @empty
                                <tr><td colspan="2">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="section">
                    <h2>Top 5 Bidang Studi</h2>
                    <table>
                        <thead><tr><th class="rank">#</th><th>Bidang</th><th class="num">Jml</th></tr></thead>
                        <tbody>
                            @forelse ($report['bidang_studi'] as $i => $row)
                                <tr><td class="rank">{{ $i + 1 }}</td><td>{{ $row['label'] }}</td><td class="num">{{ $row['total'] }}</td></tr>
                            @empty
                                <tr><td colspan="3">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="columns-2">
                <div class="section">
                    <h2>Status kepegawaian</h2>
                    <table>
                        <thead><tr><th>Status</th><th class="num">Jumlah</th><th class="num">Persen</th></tr></thead>
                        <tbody>
                            @forelse ($report['kepegawaian'] as $row)
                                <tr>
                                    <td>{{ $row['label'] }}</td>
                                    <td class="num">{{ $row['total'] }}</td>
                                    <td class="num">{{ number_format($row['persen'], 1, ',', '.') }}%</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="section">
                    <h2>Top 5 Kampus Asal</h2>
                    <table>
                        <thead><tr><th class="rank">#</th><th>Kampus</th><th class="num">Jml</th></tr></thead>
                        <tbody>
                            @forelse ($report['kampus'] as $i => $row)
                                <tr><td class="rank">{{ $i + 1 }}</td><td>{{ $row['label'] }}</td><td class="num">{{ $row['total'] }}</td></tr>
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
