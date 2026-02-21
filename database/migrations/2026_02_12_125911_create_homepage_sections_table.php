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
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // hero, cta_cards, video, features, package_cta
            $table->string('title')->nullable(); // Internal admin title
            $table->string('slug')->nullable()->unique(); // For anchor links
            $table->json('content'); // Flexible JSON content
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('scope_type')->default('global'); // global, dpw, dpc
            $table->unsignedBigInteger('scope_id')->nullable(); // For future multi-landing
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
