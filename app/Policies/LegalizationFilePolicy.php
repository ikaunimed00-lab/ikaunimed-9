<?php

namespace App\Policies;

use App\Models\LegalizationFile;
use App\Models\User;

class LegalizationFilePolicy
{
    public function view(User $user, LegalizationFile $file): bool
    {
        if ($user->can('cms.legalization.view')) {
            return true;
        }

        return $file->legalization?->user_id === $user->id;
    }
}
