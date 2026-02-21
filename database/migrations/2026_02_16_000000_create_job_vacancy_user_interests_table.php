<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_vacancy_user_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_vacancy_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['job_vacancy_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancy_user_interests');
    }
};

