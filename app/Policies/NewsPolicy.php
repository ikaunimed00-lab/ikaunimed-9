<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    /**
     * admin-toc holds cms.news.publish for their own news only, so it never grants global access.
     */
    private function canPublish(User $user): bool
    {
        if ($user->hasSystemRole(['super_admin', 'admin'])) {
            return true;
        }

        return $user->can('cms.news.publish') && ! $user->isTocNewsAuthor();
    }

    private function owns(User $user, News $news): bool
    {
        return $news->user_id === $user->id;
    }

    public function view(User $user, News $news): bool
    {
        if ($this->canPublish($user)) {
            return true;
        }

        return $user->can('cms.news.view') && $this->owns($user, $news);
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

        return $user->can('cms.news.edit') && $this->owns($user, $news);
    }

    public function delete(User $user, News $news): bool
    {
        if ($this->canPublish($user)) {
            return true;
        }

        return $user->isTocNewsAuthor() && $this->owns($user, $news);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canPublish($user);
    }

    public function restore(User $user, News $news): bool
    {
        return $this->delete($user, $news);
    }

    public function restoreAny(User $user): bool
    {
        return $this->canPublish($user);
    }

    public function forceDelete(User $user, News $news): bool
    {
        return $this->canPublish($user);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->canPublish($user);
    }
}
