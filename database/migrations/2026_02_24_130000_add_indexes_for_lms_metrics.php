<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $indexExists = static function (string $table, string $indexName): bool {
            $result = DB::selectOne(
                'SHOW INDEX FROM `' . $table . '` WHERE Key_name = ? LIMIT 1',
                [$indexName],
            );

            return $result !== null;
        };

        Schema::table('payments', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('payments', 'payments_paid_at_index')) {
                $table->index('paid_at', 'payments_paid_at_index');
            }
        });

        Schema::table('payment_logs', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('payment_logs', 'payment_logs_status_created_index')) {
                $table->index(['status_code', 'created_at'], 'payment_logs_status_created_index');
            }

            if (! $indexExists('payment_logs', 'payment_logs_created_at_index')) {
                $table->index('created_at', 'payment_logs_created_at_index');
            }
        });

        Schema::table('enrollments', function (Blueprint $table) use ($indexExists) {
            if (! $indexExists('enrollments', 'enrollments_created_at_index')) {
                $table->index('created_at', 'enrollments_created_at_index');
            }

            if (! $indexExists('enrollments', 'enrollments_completed_at_index')) {
                $table->index('completed_at', 'enrollments_completed_at_index');
            }
        });

        // Use a prefix index on status to stay compatible with older MySQL key limits.
        if (! $indexExists('enrollments', 'enrollments_course_status_index')) {
            DB::statement(
                'ALTER TABLE enrollments ADD INDEX enrollments_course_status_index (course_id, status(100))'
            );
        }
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

