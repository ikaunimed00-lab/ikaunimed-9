<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Production runs MyISAM (1000-byte index keys), so indexed strings stay at 100 characters.
     * A first attempt on production stopped after creating the table, hence the repair branch.
     */
    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('aksi', 100)->index();
                $table->string('subject_type', 100)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->json('perubahan')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamp('created_at')->nullable()->index();

                $table->index(['subject_type', 'subject_id']);
            });

            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('subject_type', 100)->nullable()->change();
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasIndex('audit_logs', ['aksi'])) {
                $table->index('aksi');
            }

            if (! Schema::hasIndex('audit_logs', ['created_at'])) {
                $table->index('created_at');
            }

            if (! Schema::hasIndex('audit_logs', ['subject_type', 'subject_id'])) {
                $table->index(['subject_type', 'subject_id']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
