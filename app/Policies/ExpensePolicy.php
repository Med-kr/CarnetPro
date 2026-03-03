<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\Flatshare;
use App\Models\User;

class ExpensePolicy
{
    public function create(User $user, Flatshare $flatshare): bool
    {
        if ($user->is_global_admin) {
            return true;
        }

        return $flatshare->memberships()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->is_global_admin || $expense->flatshare->owner_id === $user->id;
    }
}
