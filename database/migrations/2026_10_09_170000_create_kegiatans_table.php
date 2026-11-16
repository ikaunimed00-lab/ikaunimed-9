<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('image')->nullable();
            $table->string('image_sm')->nullable();
            $table->string('route_map_image')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('hero_image_mobile')->nullable();
            $table->string('theme', 20)->default('hijau');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->boolean('featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatans');
    }
};
