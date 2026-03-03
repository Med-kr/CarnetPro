<?php

namespace App\Providers;

use App\Models\Flatshare;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('flatshare-view', function (User $user, Flatshare $flatshare): bool {
            if ($user->isGlobalAdmin()) {
                return true;
            }

            return $user->isActiveMemberOfFlatshare($flatshare);
        });

        Gate::define('flatshare-manage', function (User $user, Flatshare $flatshare): bool {
            if ($user->isGlobalAdmin()) {
                return true;
            }

            return $user->isOwnerOfFlatshare($flatshare);
        });
    }
}
