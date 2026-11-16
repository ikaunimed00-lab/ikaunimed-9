<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toc_berkas', function (Blueprint $table) {
            $table->string('uploaded_ip', 45)->nullable()->after('uploaded_at');
            $table->string('uploaded_ua', 500)->nullable()->after('uploaded_ip');
        });
    }

    public function down(): void
    {
        Schema::table('toc_berkas', function (Blueprint $table) {
            $table->dropColumn(['uploaded_ip', 'uploaded_ua']);
        });
    }
};
