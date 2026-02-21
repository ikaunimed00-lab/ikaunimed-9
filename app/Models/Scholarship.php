<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Scholarship extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'deadline' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (! $model->slug) {
                $model->slug = Str::slug($model->title . '-' . Str::random(6));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applicants()
    {
        return $this->hasMany(ScholarshipApplicant::class);
    }
}
