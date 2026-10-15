<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalizationFile extends Model
{
    use HasFactory;

    public const TYPE_IJAZAH = 'ijazah';

    protected $fillable = [
        'legalization_id',
        'type',
        'filename',
        'original_name',
        'mime_type',
        'size',
    ];

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
