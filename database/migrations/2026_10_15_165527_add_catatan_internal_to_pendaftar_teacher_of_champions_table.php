<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftar_teacher_of_champions', function (Blueprint $table) {
            $table->text('catatan_internal')->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar_teacher_of_champions', function (Blueprint $table) {
            $table->dropColumn('catatan_internal');
        });
    }
};
