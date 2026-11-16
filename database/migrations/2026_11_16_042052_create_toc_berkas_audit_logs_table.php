<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toc_berkas_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40);
            $table->unsignedInteger('peserta_count')->default(0);
            $table->unsignedInteger('diterima_count')->default(0);
            $table->unsignedInteger('ditolak_count')->default(0);
            $table->unsignedInteger('dilewati_count')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toc_berkas_audit_logs');
    }
};
