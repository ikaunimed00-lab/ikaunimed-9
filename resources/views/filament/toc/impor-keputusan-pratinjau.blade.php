@php
    /** @var array{rows: int, terima: int, tolak: int, dilewati: int, sudah_sesuai: int, errors: list<array{row: int, message: string}>, fatal: string|null}|null $summary */
@endphp

<div class="space-y-4">
    @if ($summary === null)
        <p class="text-sm text-danger-600">File impor tidak ditemukan. Ulangi unggah file.</p>
    @elseif ($summary['fatal'])
        <p class="text-sm text-danger-600">{{ $summary['fatal'] }}</p>
    @else
        <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-700">
                <dt class="text-xs text-gray-500">Akan diterima</dt>
                <dd class="text-xl font-semibold text-success-600">{{ $summary['terima'] }}</dd>
            </div>
            <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-700">
                <dt class="text-xs text-gray-500">Akan ditolak</dt>
                <dd class="text-xl font-semibold text-danger-600">{{ $summary['tolak'] }}</dd>
            </div>
            <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-700">
                <dt class="text-xs text-gray-500">Dilewati (kosong)</dt>
                <dd class="text-xl font-semibold">{{ $summary['dilewati'] }}</dd>
            </div>
            <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-700">
                <dt class="text-xs text-gray-500">Sudah sesuai</dt>
                <dd class="text-xl font-semibold">{{ $summary['sudah_sesuai'] }}</dd>
            </div>
        </dl>

        @if (count($summary['errors']) > 0)
            <div>
                <p class="text-sm font-semibold text-danger-600">
                    {{ count($summary['errors']) }} kesalahan (baris ini akan dilewati)
                </p>
                <ul class="mt-2 max-h-64 space-y-1 overflow-y-auto text-sm">
                    @foreach (array_slice($summary['errors'], 0, 100) as $error)
                        <li>Baris {{ $error['row'] }}: {{ $error['message'] }}</li>
                    @endforeach
                    @if (count($summary['errors']) > 100)
                        <li class="text-gray-500">… dan {{ count($summary['errors']) - 100 }} kesalahan lainnya.</li>
                    @endif
                </ul>
            </div>
        @endif

        @if (($summary['terima'] + $summary['tolak']) === 0)
            <p class="text-sm text-gray-500">Tidak ada perubahan yang dapat diterapkan.</p>
        @endif
    @endif
</div>
