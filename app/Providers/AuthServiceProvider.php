<?php

namespace App\Providers;

use App\Models\Colocation;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('colocation-view', function (User $user, Colocation $colocation) {
            if ($user->isGlobalAdmin()) {
                return true;
            }

            return $user->isActiveMemberOfColocation($colocation->id);
        });

        Gate::define('colocation-manage', function (User $user, Colocation $colocation) {
            if ($user->isGlobalAdmin()) {
                return true;
            }

            return $user->isOwnerOfColocation($colocation->id);
        });
    }
}
