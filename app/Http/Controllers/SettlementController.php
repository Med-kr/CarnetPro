<?php

namespace App\Http\Controllers;

use App\Models\Flatshare;
use App\Services\SettlementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettlementController extends Controller
{
    public function __construct(
        protected SettlementService $settlementService
    ) {
    }

    public function show(Request $request, Flatshare $flatshare): View
    {
        $this->authorize('view', $flatshare);

        return view('flatshares.settlements', [
            'flatshare' => $flatshare->load([
                'owner',
                'payments.fromUser',
                'payments.toUser',
            ]),
            'balances' => $this->settlementService->calculateBalances($flatshare),
            'settlements' => $this->settlementService->calculateSettlements($flatshare),
        ]);
    }
}
