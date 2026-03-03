<?php

namespace App\Services;

use App\Models\Adjustment;
use App\Models\Flatshare;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    public function __construct(
        protected SettlementService $settlementService
    ) {
    }

    public function handleMemberExit(Membership $membership, bool $removedByOwner = false): void
    {
        $membership->loadMissing('user', 'flatshare.owner');

        $flatshare = $membership->flatshare;
        $debt = $this->settlementService->outstandingDebtForUser($flatshare, $membership->user);

        DB::transaction(function () use ($membership, $removedByOwner, $debt, $flatshare) {
            if ($removedByOwner && $this->hasDebt($debt)) {
                Adjustment::create([
                    'flatshare_id' => $membership->flatshare_id,
                    'from_user_id' => $membership->user_id,
                    'to_user_id' => $membership->flatshare->owner_id,
                    'amount' => $debt,
                    'reason' => 'Owner took over member debt on removal.',
                ]);
            } elseif ($this->hasDebt($debt)) {
                $this->redistributeDebtAcrossActiveMembers($flatshare, $membership, $debt);
            }

            $this->applyReputationChange($membership->user, $debt);

            $membership->update(['left_at' => now()]);
        });
    }

    public function handleFlatshareCancellation(Flatshare $flatshare): void
    {
        $balances = $this->settlementService->calculateBalances($flatshare)
            ->keyBy('user.id');

        $flatshare->activeMemberships()
            ->with('user')
            ->get()
            ->each(function (Membership $membership) use ($balances): void {
                $debt = abs(min(0, (float) data_get($balances->get($membership->user_id), 'balance', 0)));

                $this->applyReputationChange($membership->user, $debt);
            });
    }

    protected function applyReputationChange(User $user, float $debt): void
    {
        $user->increment('reputation', $this->hasDebt($debt) ? -1 : 1);
    }

    protected function hasDebt(float $debt): bool
    {
        return round($debt, 2) > 0.00;
    }

    protected function redistributeDebtAcrossActiveMembers(Flatshare $flatshare, Membership $membership, float $debt): void
    {
        $remainingMemberships = $flatshare->activeMemberships()
            ->where('user_id', '!=', $membership->user_id)
            ->with('user')
            ->get();

        if ($remainingMemberships->isEmpty()) {
            return;
        }

        $shares = $this->splitAmountAcrossMembers($debt, $remainingMemberships->count());

        foreach ($remainingMemberships->values() as $index => $remainingMembership) {
            $share = $shares[$index] ?? 0.0;

            if ($share <= 0) {
                continue;
            }

            Adjustment::create([
                'flatshare_id' => $flatshare->id,
                'from_user_id' => $membership->user_id,
                'to_user_id' => $remainingMembership->user_id,
                'amount' => $share,
                'reason' => 'Debt redistributed after member exit.',
            ]);
        }
    }

    protected function splitAmountAcrossMembers(float $amount, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $baseShare = floor(($amount / $count) * 100) / 100;
        $shares = array_fill(0, $count, $baseShare);
        $remainder = (int) round(($amount - ($baseShare * $count)) * 100);

        for ($index = 0; $index < $remainder; $index++) {
            $shares[$index] = round($shares[$index] + 0.01, 2);
        }

        return array_map(fn (float $share) => round($share, 2), $shares);
    }
}
