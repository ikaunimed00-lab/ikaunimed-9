@php
    /** @var array{token?: string, total_peserta?: int, total_bagian?: int, parts?: list<array{bagian: int, dari: int, sampai: int, jumlah: int, label: string, url: string}>, error?: string}|null $manifest */
@endphp

<div class="space-y-4">
    @if (! empty($manifest['error']))
        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $manifest['error'] }}</p>
    @else
        <p class="text-sm text-gray-600 dark:text-gray-300">
            {{ $manifest['total_peserta'] }} peserta dibagi menjadi {{ $manifest['total_bagian'] }} bagian
            (maks. {{ (int) config('toc.berkas.export_chunk_size', 50) }} peserta per ZIP).
            Unduh tiap bagian secara langsung — tanpa antrean.
        </p>

        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($manifest['parts'] as $part)
                <a
                    href="{{ $part['url'] }}"
                    class="fi-btn fi-btn-color-primary fi-size-md inline-flex items-center justify-center gap-2 rounded-lg px-3 py-2.5 text-sm font-semibold"
                >
                    {{ $part['label'] }}
                </a>
            @endforeach
        </div>
    @endif
</div>
