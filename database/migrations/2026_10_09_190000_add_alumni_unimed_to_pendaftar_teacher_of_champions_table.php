<?php

use App\Models\PendaftarTeacherOfChampions;
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
        Schema::table('pendaftar_teacher_of_champions', function (Blueprint $table) {
            $table->boolean('alumni_unimed')->nullable()->after('asal_universitas')->index();
        });

        PendaftarTeacherOfChampions::query()
            ->select(['id', 'asal_universitas'])
            ->chunkById(200, function ($pendaftar): void {
                foreach ($pendaftar as $item) {
                    PendaftarTeacherOfChampions::query()
                        ->whereKey($item->id)
                        ->update(['alumni_unimed' => PendaftarTeacherOfChampions::isAlumniUnimed($item->asal_universitas)]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pendaftar_teacher_of_champions', function (Blueprint $table) {
            $table->dropIndex(['alumni_unimed']);
            $table->dropColumn('alumni_unimed');
        });
    }
};
