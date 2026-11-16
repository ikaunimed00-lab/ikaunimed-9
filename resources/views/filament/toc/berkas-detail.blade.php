@php
    use App\Models\TocBerkas;
    /** @var \App\Models\PendaftarTeacherOfChampions $record */
    $byJenis = $record->berkas->keyBy('jenis');
@endphp

<div class="space-y-4">
    @foreach (TocBerkas::JENIS_LABELS as $jenis => $label)
        @php $berkas = $byJenis->get($jenis); @endphp
        <div class="rounded-xl border border-gray-200 p-4 dark:border-gray-700">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <p class="font-semibold">{{ $label }}</p>
                    @if ($berkas)
                        <p class="text-sm text-gray-500">
                            {{ $berkas->nama_asli }} · {{ TocBerkas::STATUS_LABELS[$berkas->status] ?? $berkas->status }}
                            @if ($berkas->uploaded_at)
                                · {{ $berkas->uploaded_at->timezone(config('toc.timezone'))->format('d M Y H:i') }} WIB
                            @endif
                        </p>
                        @if ($berkas->verified_at)
                            <p class="text-xs text-gray-500">
                                Diverifikasi oleh {{ $berkas->verifier?->name ?? '-' }}
                                pada {{ $berkas->verified_at->timezone(config('toc.timezone'))->format('d M Y H:i') }} WIB
                            </p>
                        @endif
                        @if ($berkas->catatan)
                            <p class="mt-1 text-sm text-danger-600">Catatan: {{ $berkas->catatan }}</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">Belum diunggah</p>
                    @endif
                </div>

                @if ($berkas)
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('admin.toc.berkas.download', $berkas) }}"
                            class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-size-sm fi-btn-color-gray fi-btn-outline px-3 py-2 text-sm"
                        >
                            Unduh
                        </a>
                        <form method="POST" action="{{ route('admin.toc.berkas.terima', $berkas) }}">
                            @csrf
                            <button type="submit" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-size-sm bg-success-600 text-white px-3 py-2 text-sm">
                                Terima
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.toc.berkas.tolak', $berkas) }}" class="flex gap-2">
                            @csrf
                            <input type="text" name="catatan" required placeholder="Catatan penolakan" class="rounded-lg border px-2 py-1 text-sm dark:bg-gray-900" />
                            <button type="submit" class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-size-sm bg-danger-600 text-white px-3 py-2 text-sm">
                                Tolak
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
