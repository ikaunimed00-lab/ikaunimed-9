@php
    /** @var string $text */
@endphp

<div
    x-data="{
        text: @js($text),
        status: '',
        async copy() {
            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(this.text);
                } else {
                    this.$refs.area.focus();
                    this.$refs.area.select();
                    document.execCommand('copy');
                }
                this.status = 'Tersalin';
                await $wire.recordLaporanShare('whatsapp');
            } catch (e) {
                this.status = 'Gagal otomatis — pilih semua teks di bawah lalu salin manual.';
                this.$refs.area.focus();
                this.$refs.area.select();
            }
        }
    }"
    class="space-y-3 w-full min-w-0 sm:min-w-[480px]"
>
    <p class="text-sm text-gray-600 dark:text-gray-300">
        Teks tanpa data pribadi. Tekan <strong>Salin</strong>, lalu tempel di WhatsApp.
    </p>

    <textarea
        x-ref="area"
        readonly
        rows="16"
        class="w-full min-h-[22rem] rounded-xl border border-gray-300 bg-white p-4 font-mono text-sm leading-relaxed text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"
    >{{ $text }}</textarea>

    <div class="flex flex-wrap items-center gap-3">
        <button
            type="button"
            x-on:click="copy()"
            class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-emerald-500"
        >
            Salin
        </button>
        <span
            class="text-sm font-semibold text-emerald-700 dark:text-emerald-400"
            x-show="status"
            x-text="status"
        ></span>
    </div>
</div>
