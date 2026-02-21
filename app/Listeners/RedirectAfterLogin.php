<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Redirect;

class RedirectAfterLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if ($user->hasSystemRole(['admin', 'editor', 'writer'])) {
            Redirect::setIntendedUrl('/admin');
            return;
        }

        Redirect::setIntendedUrl(route('dashboard.subscriber'));
    }
}
