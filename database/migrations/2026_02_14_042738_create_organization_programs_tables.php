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
        Schema::create('organization_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('status')->default('active'); // active, paused, archived
            $table->string('visibility')->default('both'); // public, dashboard, both
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('program_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('organization_programs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->default('member'); // admin, operator, member
            $table->timestamps();

            $table->unique(['program_id', 'user_id']); // One role per program per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_members');
        Schema::dropIfExists('organization_programs');
    }
};
