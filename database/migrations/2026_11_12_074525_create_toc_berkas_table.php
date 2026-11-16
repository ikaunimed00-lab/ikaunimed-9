<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toc_berkas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftar_id')
                ->constrained('pendaftar_teacher_of_champions')
                ->cascadeOnDelete();
            $table->string('jenis', 32);
            $table->string('path');
            $table->string('nama_asli');
            $table->string('mime', 100);
            $table->unsignedBigInteger('ukuran');
            $table->string('status', 20)->default('menunggu');
            $table->text('catatan')->nullable();
            $table->timestamp('uploaded_at');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['pendaftar_id', 'jenis']);
            $table->index('status');
            $table->index('jenis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toc_berkas');
    }
};
