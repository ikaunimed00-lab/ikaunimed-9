<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TocBerkasRejectionMailLog extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENDING = 'sending';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    /**
     * @var array<string, string>
     */
    public const STATUS_LABELS = [
        self::STATUS_QUEUED => 'Dalam antrean',
        self::STATUS_SENDING => 'Mengirim',
        self::STATUS_SENT => 'Terkirim',
        self::STATUS_FAILED => 'Gagal',
        self::STATUS_SKIPPED => 'Dilewati',
    ];

    protected $fillable = [
        'pendaftar_id',
        'berkas_id',
        'verified_at',
        'status',
        'keterangan',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function pendaftar(): BelongsTo
    {
        return $this->belongsTo(PendaftarTeacherOfChampions::class, 'pendaftar_id');
    }

    public function berkas(): BelongsTo
    {
        return $this->belongsTo(TocBerkas::class, 'berkas_id');
    }
}
