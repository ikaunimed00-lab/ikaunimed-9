<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftar_teacher_of_champions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pendaftaran', 20)->unique();
            $table->string('nama');
            $table->string('asal_instansi');
            $table->string('whatsapp', 20)->unique();
            $table->string('email')->unique();
            $table->string('provinsi');
            $table->string('kabupaten_kota');
            $table->unsignedSmallInteger('lama_mengabdi_tahun');
            $table->string('instagram', 50);
            $table->string('bidang_studi');
            $table->string('asal_universitas');
            $table->string('jenjang', 10)->nullable();
            $table->string('status_kepegawaian', 10)->nullable();
            $table->boolean('persetujuan_data')->default(false);
            $table->string('status', 20)->default('baru');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('provinsi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftar_teacher_of_champions');
    }
};
