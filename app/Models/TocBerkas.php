<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TocBerkas extends Model
{
    public const JENIS_FOTO = 'foto';

    public const JENIS_PAKTA = 'pakta_integritas';

    public const JENIS_SURAT = 'surat_keterangan';

    public const STATUS_MENUNGGU = 'menunggu';

    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_DITOLAK = 'ditolak';

    /**
     * @var array<string, string>
     */
    public const JENIS_LABELS = [
        self::JENIS_FOTO => 'Foto terbaik guru',
        self::JENIS_PAKTA => 'Pakta Integritas',
        self::JENIS_SURAT => 'Surat Keterangan Sebagai Guru',
    ];

    /**
     * @var array<string, string>
     */
    public const STATUS_LABELS = [
        self::STATUS_MENUNGGU => 'Menunggu',
        self::STATUS_DITERIMA => 'Diterima',
        self::STATUS_DITOLAK => 'Ditolak',
    ];

    protected $table = 'toc_berkas';

    protected $fillable = [
        'pendaftar_id',
        'jenis',
        'path',
        'nama_asli',
        'mime',
        'ukuran',
        'status',
        'catatan',
        'uploaded_at',
        'uploaded_ip',
        'uploaded_ua',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'ukuran' => 'integer',
            'uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function pendaftar(): BelongsTo
    {
        return $this->belongsTo(PendaftarTeacherOfChampions::class, 'pendaftar_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
