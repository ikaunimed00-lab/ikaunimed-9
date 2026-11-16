<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toc_berkas_rejection_mail_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftar_id')
                ->constrained('pendaftar_teacher_of_champions')
                ->cascadeOnDelete();
            $table->foreignId('berkas_id')
                ->constrained('toc_berkas')
                ->cascadeOnDelete();
            $table->timestamp('verified_at');
            $table->string('status', 20)->default('queued');
            $table->string('keterangan')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['berkas_id', 'verified_at'], 'toc_rejection_mail_berkas_fingerprint_unique');
            $table->index(['pendaftar_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toc_berkas_rejection_mail_logs');
    }
};
