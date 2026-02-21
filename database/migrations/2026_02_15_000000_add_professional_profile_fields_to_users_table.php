<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bidang_pekerjaan')->nullable()->after('occupation');
            $table->string('posisi_saat_ini')->nullable()->after('bidang_pekerjaan');
            $table->string('perusahaan')->nullable()->after('posisi_saat_ini');
            $table->string('kota_profesional')->nullable()->after('perusahaan');
            $table->string('status_pekerjaan')->nullable()->after('kota_profesional');
            $table->text('ringkasan_profesional')->nullable()->after('status_pekerjaan');
            $table->string('linkedin_url')->nullable()->after('ringkasan_profesional');
            $table->string('website_url')->nullable()->after('linkedin_url');
            $table->string('skills')->nullable()->after('website_url');
            $table->boolean('public_profile')->default(false)->after('skills');
            $table->unsignedTinyInteger('profile_level')->default(0)->after('public_profile');
            $table->unsignedInteger('profile_completion_score')->default(0)->after('profile_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bidang_pekerjaan',
                'posisi_saat_ini',
                'perusahaan',
                'kota_profesional',
                'status_pekerjaan',
                'ringkasan_profesional',
                'linkedin_url',
                'website_url',
                'skills',
                'public_profile',
                'profile_level',
                'profile_completion_score',
            ]);
        });
    }
};

