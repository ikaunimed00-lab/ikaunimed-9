<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('toc_berkas_export_artifacts', function (Blueprint $table) {
            $table->uuid('batch_id')->nullable()->after('token')->index();
            $table->unsignedSmallInteger('part_number')->default(1)->after('peserta_count');
            $table->unsignedSmallInteger('part_total')->default(1)->after('part_number');
        });
    }

    public function down(): void
    {
        Schema::table('toc_berkas_export_artifacts', function (Blueprint $table) {
            $table->dropColumn(['batch_id', 'part_number', 'part_total']);
        });
    }
};
