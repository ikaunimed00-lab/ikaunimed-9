<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @var array<string, list<string>>
     */
    private const INDEXES = [
        'aksi' => ['aksi'],
        'created_at' => ['created_at'],
        'subject' => ['subject_type', 'subject_id'],
    ];

    private bool $tableExists;

    /**
     * @var list<list<string>>
     */
    private array $missingIndexes;

    /**
     * The schema is inspected when the file is loaded, before `migrate --pretend` starts
     * pretending (which answers every SELECT with nothing), so the preview shows the real SQL.
     */
    public function __construct()
    {
        $this->tableExists = Schema::hasTable('audit_logs');
        $this->missingIndexes = $this->tableExists
            ? array_values(array_filter(self::INDEXES, fn (array $columns): bool => ! Schema::hasIndex('audit_logs', $columns)))
            : [];
    }

    /**
     * Production runs MyISAM (1000-byte index keys), so indexed strings stay at 100 characters.
     * A first attempt on production stopped after creating the table, hence the repair branch,
     * which only alters the existing audit_logs table and can be run again safely.
     */
    public function up(): void
    {
        if (! $this->tableExists) {
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

            foreach ($this->missingIndexes as $columns) {
                $table->index($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
