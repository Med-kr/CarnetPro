<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Flatshare;
use App\Policies\CategoryPolicy;
use App\Policies\ExpensePolicy;
use App\Policies\FlatsharePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Flatshare::class, FlatsharePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
    }
}
