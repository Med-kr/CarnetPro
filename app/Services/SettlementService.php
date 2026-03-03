<?php

namespace App\Services;

use App\Models\Flatshare;
use App\Models\User;
use Illuminate\Support\Collection;

class SettlementService
{
    public function calculateBalances(Flatshare $flatshare): Collection
    {
        $members = $flatshare->activeMemberships()
            ->with('user')
            ->get()
            ->pluck('user')
            ->keyBy('id');

        if ($members->isEmpty()) {
            return collect();
        }

        $expenses = $flatshare->expenses()->get();
        $share = round((float) $expenses->sum('amount') / max(1, $members->count()), 2);

        return $members
            ->map(function (User $user) use ($expenses, $share) {
                $totalPaid = round((float) $expenses->where('payer_id', $user->id)->sum('amount'), 2);

                return [
                    'user' => $user,
                    'total_paid' => $totalPaid,
                    'share' => $share,
                    'balance' => round($totalPaid - $share, 2),
                ];
            })
            ->sortByDesc(fn (array $line) => $line['balance'])
            ->values();
    }

    public function calculateSettlements(Flatshare $flatshare): Collection
    {
        $balances = $this->calculateBalances($flatshare)->values();
        $debtors = $balances->filter(fn (array $line) => $line['balance'] < -0.009)->values();
        $creditors = $balances->filter(fn (array $line) => $line['balance'] > 0.009)->values();
        $settlements = collect();
        $debtorIndex = 0;
        $creditorIndex = 0;

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

            if (abs($debtors[$debtorIndex]['balance']) < 0.01) {
                $debtorIndex++;
            }

            if (abs($creditors[$creditorIndex]['balance']) < 0.01) {
                $creditorIndex++;
            }
        }

        return $settlements;
    }
}
