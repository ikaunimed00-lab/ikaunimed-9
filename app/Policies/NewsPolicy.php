<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    public function view(User $user, News $news): bool
    {
        if ($user->can('cms.news.publish')) {
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
        if ($user->can('cms.news.publish')) {
            return true;
        }

        return $user->can('cms.news.edit') && $news->user_id === $user->id;
    }

    public function delete(User $user, News $news): bool
    {
        return $user->can('cms.news.publish');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('cms.news.publish');
    }
}

