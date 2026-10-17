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
                this.status = 'Tersalin. Tempel di WhatsApp.';
                await $wire.recordLaporanShare('whatsapp');
            } catch (e) {
                this.status = 'Gagal menyalin otomatis. Silakan salin manual dari kotak di bawah.';
                this.$refs.area.focus();
                this.$refs.area.select();
            }
        }
    }"
    class="space-y-3"
>
    <p class="text-sm text-gray-600 dark:text-gray-300">
        Teks di bawah tanpa data pribadi pendaftar. Tekan tombol salin, lalu tempel di grup WhatsApp.
    </p>

    <textarea
        x-ref="area"
        readonly
        rows="14"
        class="w-full rounded-xl border border-gray-300 bg-white p-3 font-mono text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100"
        x-text="text"
    >{{ $text }}</textarea>

    <div class="flex flex-wrap items-center gap-3">
        <button
            type="button"
            x-on:click="copy()"
            class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500"
        >
            Salin ke clipboard
        </button>
        <span class="text-sm text-emerald-700 dark:text-emerald-400" x-text="status"></span>
    </div>
</div>
