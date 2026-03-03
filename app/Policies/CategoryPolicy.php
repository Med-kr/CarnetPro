<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\Flatshare;
use App\Models\User;

class CategoryPolicy
{
    public function create(User $user, Flatshare $flatshare): bool
    {
        return $user->is_global_admin || $flatshare->owner_id === $user->id;
    }

    public function update(User $user, Category $category): bool
    {
        return $user->is_global_admin || $category->flatshare->owner_id === $user->id;
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->update($user, $category);
    }
}
