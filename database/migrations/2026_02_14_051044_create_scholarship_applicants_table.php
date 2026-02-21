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
        Schema::create('scholarship_applicants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'review', 'interview', 'approved', 'rejected'])->default('pending');
            $table->text('essay')->nullable();
            $table->string('cv_path')->nullable();
            $table->json('additional_data')->nullable(); // For dynamic form fields if needed
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->unique(['scholarship_id', 'user_id']); // Prevent duplicate applications
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scholarship_applicants');
    }
};
