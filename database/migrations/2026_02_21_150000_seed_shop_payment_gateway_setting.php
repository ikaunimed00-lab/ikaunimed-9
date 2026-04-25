<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $exists = DB::table('site_settings')
            ->where('key', 'shop_payment_gateway')
            ->exists();

        if (! $exists) {
            DB::table('site_settings')->insert([
                'key' => 'shop_payment_gateway',
                'value' => 'manual',
                'type' => 'text',
                'group' => 'shop',
                'label' => 'Shop Payment Gateway',
                'description' => 'Pilih gateway pembayaran aktif untuk E-Shop (manual atau tripay).',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('site_settings')
            ->where('key', 'shop_payment_gateway')
            ->delete();
    }
};

