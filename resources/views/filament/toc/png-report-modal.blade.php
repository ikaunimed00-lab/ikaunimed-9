@php
    /** @var string $previewDataUri */
    /** @var string $filename */
@endphp

<div
    x-data="{ status: '' }"
    class="space-y-3"
>
    <p class="text-sm text-gray-600 dark:text-gray-300">
        Pratinjau laporan 4:5. Unduhan selalu 1080×1350 piksel, tanpa data pribadi.
    </p>

    <div class="flex max-h-[80vh] items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-slate-100 p-2 dark:border-gray-700 dark:bg-slate-900">
        <img
            src="{{ $previewDataUri }}"
            alt="Pratinjau laporan Teacher of Champions"
            class="h-auto max-h-[76vh] w-auto max-w-full object-contain bg-white"
        />
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button
            type="button"
            wire:click="downloadPngReport"
            class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow hover:bg-emerald-500"
        >
            Unduh PNG
        </button>
        <span class="text-xs text-gray-500">{{ $filename }}</span>
    </div>
</div>
