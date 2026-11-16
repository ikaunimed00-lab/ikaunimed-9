<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function auditLogsMigration(): object
{
    return require database_path('migrations/2026_10_15_170049_create_audit_logs_table.php');
}

it('keeps indexed strings within the MyISAM key limit', function () {
    $columns = collect(Schema::getColumns('audit_logs'))->keyBy('name');

    expect($columns['subject_type']['type'])->toContain('varchar')
        ->and(Schema::hasIndex('audit_logs', ['subject_type', 'subject_id']))->toBeTrue()
        ->and(Schema::hasIndex('audit_logs', ['aksi']))->toBeTrue()
        ->and(Schema::hasIndex('audit_logs', ['created_at']))->toBeTrue();
});

function createPartialAuditLogsTable(): void
{
    Schema::drop('audit_logs');
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('aksi', 100);
        $table->string('subject_type')->nullable();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->json('perubahan')->nullable();
        $table->string('ip', 45)->nullable();
        $table->timestamp('created_at')->nullable();
    });
}

it('previews only audit_logs changes when pretending the repair', function () {
    createPartialAuditLogsTable();
    $migration = auditLogsMigration();

    $queries = collect(DB::pretend(fn () => $migration->up()))
        ->pluck('query')
        ->filter(fn (string $sql): bool => preg_match('/^\s*(create|alter|drop|insert|update|delete|rename)\b/i', $sql) === 1);

    expect($queries)->not->toBeEmpty()
        ->and($queries->every(fn (string $sql): bool => str_contains($sql, 'audit_logs')))->toBeTrue()
        ->and($queries->contains(fn (string $sql): bool => str_starts_with(strtolower($sql), 'create table "audit_logs"')))->toBeFalse()
        ->and($queries->contains(fn (string $sql): bool => str_contains($sql, 'audit_logs_subject_type_subject_id_index')))->toBeTrue();
});

it('repairs a partially created audit_logs table without dropping it', function () {
    Schema::drop('audit_logs');
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('aksi', 100);
        $table->string('subject_type')->nullable();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->json('perubahan')->nullable();
        $table->string('ip', 45)->nullable();
        $table->timestamp('created_at')->nullable();
    });
    DB::table('audit_logs')->insert(['aksi' => 'auth.admin_login', 'created_at' => now()]);

    auditLogsMigration()->up();

    expect(DB::table('audit_logs')->count())->toBe(1)
        ->and(Schema::hasIndex('audit_logs', ['subject_type', 'subject_id']))->toBeTrue()
        ->and(Schema::hasIndex('audit_logs', ['aksi']))->toBeTrue()
        ->and(Schema::hasIndex('audit_logs', ['created_at']))->toBeTrue();

    auditLogsMigration()->up();

    expect(DB::table('audit_logs')->count())->toBe(1);
});
