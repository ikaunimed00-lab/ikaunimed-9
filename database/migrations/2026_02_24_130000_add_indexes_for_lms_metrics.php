<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->index('paid_at', 'payments_paid_at_index');
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->index(['status_code', 'created_at'], 'payment_logs_status_created_index');
            $table->index('created_at', 'payment_logs_created_at_index');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->index(['course_id', 'status'], 'enrollments_course_status_index');
            $table->index('created_at', 'enrollments_created_at_index');
            $table->index('completed_at', 'enrollments_completed_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropIndex('enrollments_course_status_index');
            $table->dropIndex('enrollments_created_at_index');
            $table->dropIndex('enrollments_completed_at_index');
        });

        Schema::table('payment_logs', function (Blueprint $table) {
            $table->dropIndex('payment_logs_status_created_index');
            $table->dropIndex('payment_logs_created_at_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_paid_at_index');
        });
    }
};

