<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Flatshare;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $this->authorize('access', User::class);

        $expenses = Expense::with(['flatshare', 'payer'])->latest()->get();

        return view('admin.index', [
            'stats' => [
                'users' => User::count(),
                'flatshares' => Flatshare::count(),
                'expenses' => $expenses->count(),
                'banned' => User::where('is_banned', true)->count(),
            ],
            'users' => User::latest()->get(),
            'flatshares' => Flatshare::with('owner')->latest()->get(),
            'expenses' => $expenses->take(20),
            'monthlyExpenseStats' => $expenses
                ->groupBy(fn ($expense) => $expense->spent_at->format('Y-m'))
                ->map(fn ($group, $month) => [
                    'month' => $month,
                    'amount' => round((float) $group->sum('amount'), 2),
                ])
                ->sortByDesc('month')
                ->values()
                ->take(6),
        ]);
    }

    public function ban(User $user): RedirectResponse
    {
        $this->authorize('access', User::class);
        abort_if($user->is_global_admin, 422, 'Global admin cannot be banned.');

        $user->update(['is_banned' => true]);

        return back()->with('success', 'User banned.');
    }

    public function unban(User $user): RedirectResponse
    {
        $this->authorize('access', User::class);

        $user->update(['is_banned' => false]);

        return back()->with('success', 'User unbanned.');
    }
}
