<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class OrganizationProgram extends Model
{
    use HasFactory;

    protected $table = 'organization_programs';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'status',
        'visibility',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'string', // active, paused, archived
        'visibility' => 'string', // public, dashboard, both
        'sort_order' => 'integer',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'program_members', 'program_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }
}
