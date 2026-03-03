<?php

namespace App\Policies;

use App\Models\Flatshare;
use App\Models\Membership;
use App\Models\User;

class FlatsharePolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->is_banned;
    }

    public function view(User $user, Flatshare $flatshare): bool
    {
        if ($user->is_global_admin) {
            return true;
        }

        return $flatshare->memberships()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();
    }

    public function create(User $user): bool
    {
        if ($user->is_banned) {
            return false;
        }

        if ($user->is_global_admin) {
            return true;
        }

        return ! $user->hasActiveFlatshare();
    }

    public function update(User $user, Flatshare $flatshare): bool
    {
        return $user->is_global_admin || $flatshare->owner_id === $user->id;
    }

    public function cancel(User $user, Flatshare $flatshare): bool
    {
        return $this->update($user, $flatshare);
    }

    public function invite(User $user, Flatshare $flatshare): bool
    {
        return $this->update($user, $flatshare) && $flatshare->isActive();
    }

    public function removeMember(User $user, Flatshare $flatshare): bool
    {
        return $this->update($user, $flatshare);
    }

    public function leave(User $user, Flatshare $flatshare): bool
    {
        $membership = $flatshare->memberships()
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->first();

        return $membership instanceof Membership && ! $membership->isOwner();
    }

    public function delete(User $user, Flatshare $flatshare): bool
    {
        return $user->is_global_admin || $flatshare->owner_id === $user->id;
    }
}
