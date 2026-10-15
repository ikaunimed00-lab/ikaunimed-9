<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalizationFile extends Model
{
    use HasFactory;

    public const TYPE_IJAZAH = 'ijazah';

    /**
     * Disk privat: dokumen ijazah tidak boleh bisa diakses lewat URL publik.
     */
    public const DISK = 'local';

    public const DIRECTORY = 'legalizations';

    protected $fillable = [
        'legalization_id',
        'type',
        'filename',
        'original_name',
        'mime_type',
        'size',
    ];

    protected $hidden = [
        'filename',
    ];

    protected $appends = [
        'download_url',
    ];

    public function storagePath(): string
    {
        return self::DIRECTORY.'/'.$this->filename;
    }

    protected function downloadUrl(): Attribute
    {
        return Attribute::get(fn (): string => route('legalization.files.download', $this));
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // File milik 1 legalisasi
    public function legalization()
    {
        return $this->belongsTo(Legalization::class);
    }
}
