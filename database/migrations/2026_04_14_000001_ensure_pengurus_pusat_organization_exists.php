<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('organizations')->updateOrInsert(
            ['slug' => 'pp'],
            [
                'name' => 'Pengurus Pusat',
                'type' => 'pp',
                'parent_id' => null,
                'logo' => null,
                'description' => null,
                'address' => null,
                'phone' => null,
                'email' => null,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('organizations')->where('slug', 'pp')->delete();
    }
};

