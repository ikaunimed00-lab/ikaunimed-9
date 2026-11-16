@php
    /** @var \Illuminate\Support\Collection<int, array{label: string, dibuat: string, pesertaCount: int, berlakuSampai: string, url: string}> $artifacts */
@endphp

<div class="space-y-3">
    @forelse ($artifacts as $artifact)
        <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-gray-200 p-3 dark:border-gray-700">
            <div>
                <p class="text-sm font-semibold">{{ $artifact['label'] }} · {{ $artifact['pesertaCount'] }} peserta</p>
                <p class="text-xs text-gray-500">Dibuat {{ $artifact['dibuat'] }} WIB · berlaku sampai {{ $artifact['berlakuSampai'] }} WIB</p>
            </div>
            <a href="{{ $artifact['url'] }}" class="fi-btn fi-size-sm fi-btn-color-primary rounded-lg px-3 py-2 text-sm font-semibold">
                Unduh ZIP
            </a>
        </div>
    @empty
        <p class="text-sm text-gray-500">Belum ada ekspor aktif. Pilih peserta lalu gunakan "Unduh ZIP berkas + Excel keputusan"; tautan berlaku 1 jam. Bila baru saja meminta, tunggu antrian selesai lalu buka lagi menu ini.</p>
    @endforelse
</div>
