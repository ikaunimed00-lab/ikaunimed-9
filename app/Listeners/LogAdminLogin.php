<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Login;

class LogAdminLogin
{
    /**
     * @var list<string>
     */
    public const ADMIN_ROLES = ['super_admin', 'admin', 'editor', 'writer', User::ROLE_ADMIN_TOC, User::ROLE_PANITIA_TOC];

    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User || ! $user->hasSystemRole(self::ADMIN_ROLES)) {
            return;
        }

        $this->auditLogger->record('auth.admin_login', $user, [
            'guard' => $event->guard,
            'roles' => $user->getRoleNames()->values()->all(),
        ], $user->id);
    }
}
