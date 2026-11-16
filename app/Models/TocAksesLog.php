<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TocAksesLog extends Model
{
    public $timestamps = false;

    protected $table = 'toc_akses_log';

    protected $fillable = [
        'nomor_pendaftaran',
        'ip',
        'user_agent',
        'hasil',
        'sumber',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
