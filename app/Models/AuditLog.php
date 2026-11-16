<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /** @use HasFactory<\Database\Factories\AuditLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var array<string, string>
     */
    public const ACTIONS = [
        'pendaftar.updated' => 'Ubah pendaftar',
        'pendaftar.exported' => 'Ekspor pendaftar',
        'pendaftar.dilihat' => 'Lihat pendaftar',
        'pendaftar.dicetak' => 'Cetak pendaftar',
        'kegiatan.updated' => 'Ubah kegiatan ToC',
        'news.created' => 'Buat berita',
        'news.updated' => 'Ubah berita',
        'news.published' => 'Terbitkan berita',
        'auth.admin_login' => 'Login admin',
        'laporan.dibagikan' => 'Bagikan laporan ToC',
    ];

    protected $fillable = [
        'user_id',
        'aksi',
        'subject_type',
        'subject_id',
        'perubahan',
        'ip',
    ];

    protected function casts(): array
    {
        return [
            'perubahan' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
