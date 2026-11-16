<x-mail::message>
# Halo, {{ $nama }}

Terima kasih telah mengunggah berkas finalis Teacher of Champions 2026. Setelah diperiksa panitia, berkas berikut **belum dapat diterima** dan perlu diunggah ulang:

@foreach ($items as $item)
- **{{ $item['label'] }}** — {{ $item['catatan'] }}
@endforeach

## Cara mengunggah ulang

1. Buka [{{ $reuploadUrl }}]({{ $reuploadUrl }}).
2. Masuk menggunakan nomor pendaftaran Anda.
3. Unggah ulang berkas pada kartu berkas yang berstatus **Ditolak**.

**Tenggat unggah ulang: {{ $batasLabel }}.**

## Format dan ukuran berkas

@foreach ($formats as $format)
- **{{ $format['label'] }}**: {{ $format['extensions'] }}, maksimal {{ $format['maxMb'] }} MB{{ $format['resolusi'] }}
@endforeach

## Butuh bantuan?

Hubungi panitia melalui WhatsApp:

@foreach ($whatsapp as $kontak)
- [{{ $kontak['nomor'] }}]({{ $kontak['url'] }})
@endforeach

Salam,<br>
Panitia Teacher of Champions IKA UNIMED
</x-mail::message>
