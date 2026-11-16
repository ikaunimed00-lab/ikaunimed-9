<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TocBerkasExportArtifact extends Model
{
    protected $fillable = [
        'token',
        'batch_id',
        'user_id',
        'path',
        'peserta_count',
        'part_number',
        'part_total',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'peserta_count' => 'integer',
            'part_number' => 'integer',
            'part_total' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function label(): string
    {
        if ($this->part_total > 1) {
            return "Bagian {$this->part_number}/{$this->part_total}";
        }

        return 'ZIP berkas + Excel keputusan';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
