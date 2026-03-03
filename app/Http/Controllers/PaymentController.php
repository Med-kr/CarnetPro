<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Flatshare;
use App\Models\Payment;
use App\Services\SettlementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Flatshare $flatshare, SettlementService $settlementService): RedirectResponse
    {
        if (! $request->user()->is_global_admin && $request->integer('from_user_id') !== $request->user()->id) {
            abort(403);
        }

        $currentSettlementAmount = $settlementService->findSettlementAmount(
            $flatshare,
            $request->integer('from_user_id'),
            $request->integer('to_user_id')
        );

        if ($currentSettlementAmount === null) {
            throw ValidationException::withMessages([
                'amount' => 'No outstanding settlement matches this payment.',
            ]);
        }

        $paidAmount = round((float) $request->input('amount'), 2);
        $appliedAmount = round(min($paidAmount, $currentSettlementAmount), 2);
        $creditAmount = round(max(0, $paidAmount - $currentSettlementAmount), 2);

        DB::transaction(function () use ($request, $flatshare, $paidAmount, $currentSettlementAmount, $appliedAmount, $creditAmount) {
            Payment::create([
                'flatshare_id' => $flatshare->id,
                'from_user_id' => $request->integer('from_user_id'),
                'to_user_id' => $request->integer('to_user_id'),
                'amount' => $paidAmount,
                'settlement_amount' => $currentSettlementAmount,
                'applied_amount' => $appliedAmount,
                'credit_amount' => $creditAmount,
                'method' => $request->string('method')->toString(),
                'status' => $request->string('status')->toString(),
                'reference' => $request->string('reference')->toString() ?: null,
                'note' => $request->string('note')->toString() ?: null,
                'paid_at' => now(),
            ]);
        });

        return $this->paymentRedirect($request, $flatshare)->with('status', 'Payment recorded.');
    }

    protected function paymentRedirect(Request $request, Flatshare $flatshare): RedirectResponse
    {
        $redirectTo = $request->input('redirect_to');

        if (is_string($redirectTo) && str_starts_with($redirectTo, url('/'))) {
            return redirect()->to($redirectTo);
        }

        return redirect()->route('flatshares.settlements.show', $flatshare);
    }
}
