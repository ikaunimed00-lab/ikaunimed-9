@php
    /** @var array $summary */
    /** @var string $generatedAt */
    /** @var string $closesLabel */
    /** @var string $filename */
@endphp

<div
    x-data="{
        summary: {{ \Illuminate\Support\Js::from($summary) }},
        generatedAt: {{ \Illuminate\Support\Js::from($generatedAt) }},
        closesLabel: {{ \Illuminate\Support\Js::from($closesLabel) }},
        filename: {{ \Illuminate\Support\Js::from($filename) }},
        status: '',
        draw() {
            const canvas = this.$refs.canvas;
            const ctx = canvas.getContext('2d');
            const W = canvas.width;
            const H = canvas.height;
            const green = '#0B3D2E';
            const teal = '#006837';
            const gold = '#FFD700';
            const ink = '#0F172A';
            const muted = '#475569';
            const summary = this.summary;

            ctx.fillStyle = '#F8FAF9';
            ctx.fillRect(0, 0, W, H);
            ctx.fillStyle = green;
            ctx.fillRect(0, 0, W, 220);
            ctx.fillStyle = gold;
            ctx.fillRect(0, 220, W, 12);

            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 48px sans-serif';
            ctx.fillText('Teacher of Champions', 64, 100);
            ctx.font = '28px sans-serif';
            ctx.fillText('Update pendaftar IKA UNIMED', 64, 150);
            ctx.font = '24px sans-serif';
            ctx.fillStyle = gold;
            ctx.fillText(this.generatedAt, 64, 190);

            const box = (x, y, w, h, title, value) => {
                ctx.fillStyle = '#ffffff';
                ctx.strokeStyle = '#E2E8F0';
                ctx.lineWidth = 2;
                ctx.beginPath();
                if (typeof ctx.roundRect === 'function') {
                    ctx.roundRect(x, y, w, h, 24);
                } else {
                    ctx.rect(x, y, w, h);
                }
                ctx.fill();
                ctx.stroke();
                ctx.fillStyle = muted;
                ctx.font = '22px sans-serif';
                ctx.fillText(title, x + 28, y + 42);
                ctx.fillStyle = teal;
                ctx.font = 'bold 56px sans-serif';
                ctx.fillText(String(value), x + 28, y + 110);
            };

            box(64, 280, 450, 150, 'Total', summary.total ?? 0);
            box(566, 280, 450, 150, 'Hari ini (WIB)', summary.today ?? 0);

            ctx.fillStyle = ink;
            ctx.font = 'bold 30px sans-serif';
            ctx.fillText('Status', 64, 500);
            ctx.font = '24px sans-serif';
            ctx.fillStyle = muted;
            const statuses = summary.statuses || {};
            ctx.fillText(
                'baru ' + (statuses.baru ?? 0) + ' · terverifikasi ' + (statuses.terverifikasi ?? 0) + ' · lolos ' + (statuses.lolos ?? 0) + ' · ditolak ' + (statuses.ditolak ?? 0),
                64,
                545
            );

            ctx.fillStyle = ink;
            ctx.font = 'bold 30px sans-serif';
            ctx.fillText('Alumni UNIMED: ' + (summary.alumni ?? 0) + ' (' + (summary.alumni_percentage ?? 0) + '%)', 64, 610);

            ctx.fillStyle = ink;
            ctx.font = 'bold 30px sans-serif';
            ctx.fillText('Tren harian (WIB)', 64, 680);

            const daily = Object.entries(summary.daily || {});
            const chartX = 64;
            const chartY = 720;
            const chartW = W - 128;
            const chartH = 180;
            const max = Math.max(1, ...daily.map((entry) => Number(entry[1])));

            ctx.strokeStyle = '#CBD5E1';
            ctx.beginPath();
            ctx.moveTo(chartX, chartY + chartH);
            ctx.lineTo(chartX + chartW, chartY + chartH);
            ctx.stroke();

            if (daily.length > 0) {
                const step = chartW / Math.max(daily.length - 1, 1);
                ctx.strokeStyle = teal;
                ctx.lineWidth = 4;
                ctx.beginPath();
                daily.forEach((entry, i) => {
                    const x = chartX + i * step;
                    const y = chartY + chartH - (Number(entry[1]) / max) * chartH;
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                });
                ctx.stroke();
            }

            const listBlock = (title, rows, y) => {
                ctx.fillStyle = ink;
                ctx.font = 'bold 28px sans-serif';
                ctx.fillText(title, 64, y);
                ctx.font = '24px sans-serif';
                ctx.fillStyle = muted;
                (rows || []).slice(0, 3).forEach((row, i) => {
                    ctx.fillText((i + 1) + ') ' + row.label + ' — ' + row.total, 64, y + 40 + i * 34);
                });
            };

            listBlock('Top provinsi', summary.provinces, 960);
            listBlock('Jenjang', summary.jenjang, 1100);
            listBlock('Bidang studi', summary.bidang_studi, 1220);

            ctx.fillStyle = green;
            ctx.fillRect(0, H - 70, W, 70);
            ctx.fillStyle = gold;
            ctx.font = 'bold 22px sans-serif';
            ctx.fillText(this.closesLabel, 64, H - 28);
            ctx.fillText('ikaunimed.or.id/toc', W - 360, H - 28);
        },
        async download() {
            const canvas = this.$refs.canvas;
            const link = document.createElement('a');
            link.download = this.filename;
            link.href = canvas.toDataURL('image/png');
            link.click();
            this.status = 'PNG diunduh.';
            await $wire.recordLaporanShare('png');
        }
    }"
    x-init="draw()"
    class="space-y-3"
>
    <p class="text-sm text-gray-600 dark:text-gray-300">
        Pratinjau gambar 4:5 (1080×1350) untuk WhatsApp/Instagram. Tanpa data pribadi.
    </p>

    <div class="overflow-auto rounded-xl border border-gray-200 bg-slate-100 p-2 dark:border-gray-700 dark:bg-slate-900">
        <canvas x-ref="canvas" width="1080" height="1350" class="mx-auto h-auto w-full max-w-md bg-white"></canvas>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button
            type="button"
            x-on:click="download()"
            class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500"
        >
            Unduh PNG
        </button>
        <span class="text-sm text-emerald-700 dark:text-emerald-400" x-text="status"></span>
    </div>
</div>
