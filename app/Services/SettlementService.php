<?php

namespace App\Services;

use App\Models\Flatshare;
use App\Models\User;
use Illuminate\Support\Collection;

class SettlementService
{
    public function buildOverview(Flatshare $flatshare, ?string $month = null): array
    {
        $members = $flatshare->activeMemberships()
            ->with('user')
            ->get()
            ->pluck('user')
            ->keyBy('id');

        $expenses = $flatshare->expenses()
            ->with(['payer', 'category'])
            ->when($month, fn ($query) => $query->where('spent_at', 'like', $month.'%'))
            ->orderByDesc('spent_at')
            ->get();

        return [
            'members' => $members,
            'expenses' => $expenses,
            'balances' => $this->calculateBalances($flatshare, $month, $members),
            'settlements' => $this->calculateSettlements($flatshare, $month, $members),
            'expenseStats' => $this->buildExpenseStats($flatshare, $month),
        ];
    }

    public function calculateBalances(Flatshare $flatshare, ?string $month = null, ?Collection $members = null): Collection
    {
        $members ??= $flatshare->activeMemberships()->with('user')->get()->pluck('user')->keyBy('id');
        $expenses = $flatshare->expenses()
            ->when($month, fn ($query) => $query->where('spent_at', 'like', $month.'%'))
            ->get();
        $payments = $flatshare->payments()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', \App\Models\Payment::STATUS_COMPLETED);
            })
            ->get();
        $adjustments = $flatshare->adjustments()->get();

        $balances = $members->mapWithKeys(function (User $user) {
            return [$user->id => [
                'user' => $user,
                'total_paid' => 0.0,
                'share' => 0.0,
                'balance' => 0.0,
            ]];
        });

        if ($members->isEmpty()) {
            return collect();
        }

        $totalExpenses = (float) $expenses->sum('amount');
        $share = round($totalExpenses / max(1, $members->count()), 2);

        foreach ($balances as $userId => $line) {
            $totalPaid = (float) $expenses->where('payer_id', $userId)->sum('amount');
            $balances->put($userId, [
                ...$line,
                'total_paid' => $totalPaid,
                'share' => $share,
                'balance' => round($totalPaid - $share, 2),
            ]);
        }

        $this->applyTransfers($balances, $payments);
        $this->applyTransfers($balances, $adjustments);

        return $balances->map(function (array $line) {
            $line['balance'] = round($line['balance'], 2);

            return $line;
        })->sortByDesc(fn (array $line) => $line['balance'])->values();
    }

    public function calculateSettlements(Flatshare $flatshare, ?string $month = null, ?Collection $members = null): Collection
    {
        $balances = $this->calculateBalances($flatshare, $month, $members)
            ->map(fn (array $line) => $line)
            ->values();

        $debtors = $balances->filter(fn (array $line) => $line['balance'] < -0.009)->values();
        $creditors = $balances->filter(fn (array $line) => $line['balance'] > 0.009)->values();
        $settlements = collect();
        $debtorIndex = 0;
        $creditorIndex = 0;

        // Greedy matching is enough here because all shares are equal.
        while ($debtorIndex < $debtors->count() && $creditorIndex < $creditors->count()) {
            $debtor = $debtors[$debtorIndex];
            $creditor = $creditors[$creditorIndex];
            $amount = round(min(abs($debtor['balance']), $creditor['balance']), 2);

            if ($amount <= 0) {
                break;
            }

            $settlements->push([
                'from_user' => $debtor['user'],
                'to_user' => $creditor['user'],
                'amount' => $amount,
            ]);

            $debtors->put($debtorIndex, [
                ...$debtor,
                'balance' => round($debtor['balance'] + $amount, 2),
            ]);

            $creditors->put($creditorIndex, [
                ...$creditor,
                'balance' => round($creditor['balance'] - $amount, 2),
            ]);

            if (abs($debtors->get($debtorIndex)['balance']) < 0.01) {
                $debtorIndex++;
            }

            if (abs($creditors->get($creditorIndex)['balance']) < 0.01) {
                $creditorIndex++;
            }
        }

        return $settlements;
    }

    public function outstandingDebtForUser(Flatshare $flatshare, User $user): float
    {
        $line = $this->calculateBalances($flatshare)
            ->firstWhere('user.id', $user->id);

        if (! $line) {
            return 0.0;
        }

        return $line['balance'] < 0 ? abs($line['balance']) : 0.0;
    }

    protected function applyTransfers(Collection $balances, Collection $transfers): void
    {
        foreach ($transfers as $transfer) {
            if (! isset($balances[$transfer->from_user_id], $balances[$transfer->to_user_id])) {
                continue;
            }

            $from = $balances->get($transfer->from_user_id);
            $to = $balances->get($transfer->to_user_id);

            $balances->put($transfer->from_user_id, [
                ...$from,
                'balance' => round($from['balance'] + (float) $transfer->amount, 2),
            ]);

            $balances->put($transfer->to_user_id, [
                ...$to,
                'balance' => round($to['balance'] - (float) $transfer->amount, 2),
            ]);
        }
    }

    public function findSettlementAmount(Flatshare $flatshare, int $fromUserId, int $toUserId): ?float
    {
        $settlement = $this->calculateSettlements($flatshare)
            ->first(fn (array $line) => $line['from_user']->id === $fromUserId && $line['to_user']->id === $toUserId);

        return $settlement ? (float) $settlement['amount'] : null;
    }

    public function buildExpenseStats(Flatshare $flatshare, ?string $month = null): array
    {
        $expenses = $flatshare->expenses()
            ->with('category')
            ->when($month, fn ($query) => $query->where('spent_at', 'like', $month.'%'))
            ->orderByDesc('spent_at')
            ->get();

        return [
            'total_expenses' => round((float) $expenses->sum('amount'), 2),
            'expense_count' => $expenses->count(),
            'monthly_totals' => $expenses
                ->groupBy(fn ($expense) => $expense->spent_at->format('Y-m'))
                ->map(fn (Collection $group, string $month) => [
                    'month' => $month,
                    'amount' => round((float) $group->sum('amount'), 2),
                    'count' => $group->count(),
                ])
                ->sortByDesc('month')
                ->values(),
            'category_totals' => $expenses
                ->groupBy(fn ($expense) => $expense->category?->name ?? 'Uncategorized')
                ->map(fn (Collection $group, string $category) => [
                    'category' => $category,
                    'amount' => round((float) $group->sum('amount'), 2),
                    'count' => $group->count(),
                ])
                ->sortByDesc('amount')
                ->values(),
        ];
    }
}
