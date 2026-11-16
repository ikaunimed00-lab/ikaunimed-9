<?php

namespace App\Mail;

use App\Models\PendaftarTeacherOfChampions;
use App\Models\TocBerkas;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TocBerkasDitolakMail extends Mailable
{
    use Queueable, SerializesModels;

    public const REUPLOAD_URL = 'https://www.ikaunimed.or.id/toc/berkas';

    /**
     * @param  list<array{label: string, catatan: string}>  $items
     */
    public function __construct(
        public PendaftarTeacherOfChampions $peserta,
        public array $items,
        public Carbon $batas,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Berkas Teacher of Champions 2026 perlu diunggah ulang',
        );
    }

    public function content(): Content
    {
        $timezone = config('toc.timezone');

        return new Content(
            markdown: 'emails.toc-berkas-ditolak',
            with: [
                'nama' => $this->peserta->nama,
                'items' => $this->items,
                'reuploadUrl' => self::REUPLOAD_URL,
                'batasLabel' => $this->batas->copy()->timezone($timezone)->locale('id')->translatedFormat('l, j F Y \p\u\k\u\l H.i').' WIB',
                'formats' => collect(config('toc.berkas.jenis'))
                    ->map(fn (array $meta, string $jenis): array => [
                        'label' => TocBerkas::JENIS_LABELS[$jenis] ?? $meta['label'],
                        'extensions' => strtoupper(implode(', ', $meta['extensions'])),
                        'maxMb' => round(((int) $meta['max_kb']) / 1024, 1),
                        'resolusi' => isset($meta['min_width'], $meta['min_height'])
                            ? ", resolusi minimal {$meta['min_width']} x {$meta['min_height']} px"
                            : '',
                    ])
                    ->values()
                    ->all(),
                'whatsapp' => collect(config('toc.panitia_whatsapp', []))
                    ->map(fn (string $nomor): array => [
                        'nomor' => $nomor,
                        'url' => 'https://wa.me/'.PendaftarTeacherOfChampions::normalizeWhatsapp($nomor),
                    ])
                    ->all(),
            ],
        );
    }
}
