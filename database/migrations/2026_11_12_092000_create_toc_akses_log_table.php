<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('toc_akses_log', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pendaftaran', 32)->nullable()->index();
            $table->string('ip', 45)->nullable()->index();
            $table->string('user_agent', 500)->nullable();
            $table->string('hasil', 20);
            $table->string('sumber', 32)->default('pengumuman');
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('toc_akses_log');
    }
};
