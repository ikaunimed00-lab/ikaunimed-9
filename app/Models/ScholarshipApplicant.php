<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScholarshipApplicant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'additional_data' => 'array',
    ];

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
