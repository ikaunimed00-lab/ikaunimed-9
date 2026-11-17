<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user instanceof User && $user->isPanitiaBerkasOnly()) {
            return false;
        }

        return true;
    }
}
