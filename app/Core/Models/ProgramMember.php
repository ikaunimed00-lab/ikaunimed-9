<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProgramMember extends Pivot
{
    protected $table = 'program_members';

    protected $fillable = [
        'program_id',
        'user_id',
        'role',
    ];
}
