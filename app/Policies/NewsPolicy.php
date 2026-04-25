<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    private function canPublish(User $user): bool
    {
        return $user->can('cms.news.publish') || $user->hasSystemRole(['super_admin', 'admin']);
    }

    public function view(User $user, News $news): bool
    {
        if ($this->canPublish($user)) {
            return true;
        }

        return $user->can('cms.news.view') && $news->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('cms.news.create');
    }

    public function update(User $user, News $news): bool
    {
        if ($this->canPublish($user)) {
            return true;
        }

        return $user->can('cms.news.edit') && $news->user_id === $user->id;
    }

    public function delete(User $user, News $news): bool
    {
        return $this->canPublish($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canPublish($user);
    }
}
