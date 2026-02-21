<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AuditSpatieRoles extends Command
{
    protected $signature = 'rbac:audit-users-without-roles {--limit=50}';

    protected $description = 'List users that do not have any Spatie roles assigned';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $query = User::doesntHave('roles');

        $count = $query->count();

        $this->info("Users without Spatie roles: {$count}");

        if ($count === 0) {
            return static::SUCCESS;
        }

        $users = $query
            ->limit($limit)
            ->get(['id', 'name', 'email', 'role']);

        foreach ($users as $user) {
            $this->line("{$user->id} | {$user->email} | legacy role: {$user->role}");
        }

        return static::SUCCESS;
    }
}

