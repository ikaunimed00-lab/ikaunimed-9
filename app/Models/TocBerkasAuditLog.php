<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TocBerkasAuditLog extends Model
{
    public const TYPE_EXPORT_ZIP = 'export_zip';

    public const TYPE_IMPORT_KEPUTUSAN = 'import_keputusan';

    public const TYPE_ACCEPT_WAITING = 'accept_waiting';

    public const TYPE_TERIMA = 'terima';

    public const TYPE_TOLAK = 'tolak';

    protected $fillable = [
        'user_id',
        'type',
        'peserta_count',
        'diterima_count',
        'ditolak_count',
        'dilewati_count',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'peserta_count' => 'integer',
            'diterima_count' => 'integer',
            'ditolak_count' => 'integer',
            'dilewati_count' => 'integer',
            'meta' => 'array',
        ];
    }

    /**
     * @param  array{peserta?: int, diterima?: int, ditolak?: int, dilewati?: int}  $counts
     * @param  array<string, mixed>|null  $meta
     */
    public static function record(string $type, ?int $userId, array $counts = [], ?array $meta = null): self
    {
        return static::query()->create([
            'user_id' => $userId,
            'type' => $type,
            'peserta_count' => $counts['peserta'] ?? 0,
            'diterima_count' => $counts['diterima'] ?? 0,
            'ditolak_count' => $counts['ditolak'] ?? 0,
            'dilewati_count' => $counts['dilewati'] ?? 0,
            'meta' => $meta,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
