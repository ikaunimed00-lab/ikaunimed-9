<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobVacancy extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($job) {
            if (! $job->slug) {
                $job->slug = Str::slug($job->title . '-' . Str::random(6));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function interestedUsers()
    {
        return $this->belongsToMany(User::class, 'job_vacancy_user_interests')->withTimestamps();
    }
}
