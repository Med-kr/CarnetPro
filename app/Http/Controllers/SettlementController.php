<?php

namespace App\Http\Controllers;

use App\Models\Flatshare;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettlementController extends Controller
{
    public function __construct(
        protected SettlementService $settlementService
    ) {
    }

    public function show(Request $request, Flatshare $flatshare): View|RedirectResponse
    {
        if ($redirect = app(FlatshareController::class)->redirectIfCannotView($request, $flatshare)) {
            return $redirect;
        }

        return view('flatshares.settlements', [
            'flatshare' => $flatshare->load([
                'owner',
                'payments.fromUser',
                'payments.toUser',
            ]),
            'settlements' => $this->settlementService->calculateSettlements($flatshare),
            'balances' => $this->settlementService->calculateBalances($flatshare),
        ]);
    }
}
