<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftar_teacher_of_champions', function (Blueprint $table) {
            $table->boolean('lulus_penyisihan')->nullable()->default(null)->after('status');
            $table->timestamp('setuju_publikasi_at')->nullable()->after('lulus_penyisihan');
            $table->index('lulus_penyisihan');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar_teacher_of_champions', function (Blueprint $table) {
            $table->dropIndex(['lulus_penyisihan']);
            $table->dropColumn(['lulus_penyisihan', 'setuju_publikasi_at']);
        });
    }
};
