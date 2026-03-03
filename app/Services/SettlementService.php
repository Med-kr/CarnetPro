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
        $payments = $flatshare->payments()
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', \App\Models\Payment::STATUS_COMPLETED);
            })
            ->get();
        $adjustments = $flatshare->adjustments()->get();
        $share = round((float) $expenses->sum('amount') / max(1, $members->count()), 2);

        $balances = $members->mapWithKeys(function (User $user) use ($expenses, $share) {
            $totalPaid = round((float) $expenses->where('payer_id', $user->id)->sum('amount'), 2);

            return [$user->id => [
                'user' => $user,
                'total_paid' => $totalPaid,
                'share' => $share,
                'balance' => round($totalPaid - $share, 2),
            ]];
        });

        foreach ($payments as $payment) {
            if (! isset($balances[$payment->from_user_id], $balances[$payment->to_user_id])) {
                continue;
            }

            $from = $balances->get($payment->from_user_id);
            $to = $balances->get($payment->to_user_id);

            $balances->put($payment->from_user_id, [
                ...$from,
                'balance' => round($from['balance'] + (float) $payment->amount, 2),
            ]);

            $balances->put($payment->to_user_id, [
                ...$to,
                'balance' => round($to['balance'] - (float) $payment->amount, 2),
            ]);
        }

        foreach ($adjustments as $adjustment) {
            if (! isset($balances[$adjustment->from_user_id], $balances[$adjustment->to_user_id])) {
                continue;
            }

            $from = $balances->get($adjustment->from_user_id);
            $to = $balances->get($adjustment->to_user_id);

            $balances->put($adjustment->from_user_id, [
                ...$from,
                'balance' => round($from['balance'] - (float) $adjustment->amount, 2),
            ]);

            $balances->put($adjustment->to_user_id, [
                ...$to,
                'balance' => round($to['balance'] + (float) $adjustment->amount, 2),
            ]);
        }

        return $balances
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

    public function findSettlementAmount(Flatshare $flatshare, int $fromUserId, int $toUserId): ?float
    {
        $settlement = $this->calculateSettlements($flatshare)
            ->first(fn (array $line) => $line['from_user']->id === $fromUserId && $line['to_user']->id === $toUserId);

        return $settlement ? (float) $settlement['amount'] : null;
    }

    public function outstandingDebtForUser(Flatshare $flatshare, User $user): float
    {
        $balance = $this->calculateBalances($flatshare)
            ->first(fn (array $line) => $line['user']->id === $user->id);

        if ($balance === null) {
            return 0.0;
        }

        return round(abs(min(0, (float) $balance['balance'])), 2);
    }
}
