<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Redirect;

class RedirectAfterLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if ($user->hasSystemRole(['super_admin', 'admin', 'editor', 'writer', User::ROLE_ADMIN_TOC, User::ROLE_PANITIA_TOC])) {
            Redirect::setIntendedUrl('/admin');

            return;
        }

        Redirect::setIntendedUrl(route('dashboard.subscriber'));
    }
}
