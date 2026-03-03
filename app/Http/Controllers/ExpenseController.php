<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\Flatshare;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request, Flatshare $flatshare): View|RedirectResponse
    {
        return app(FlatshareController::class)->show(
            $request->merge(['tab' => 'expenses']),
            $flatshare
        );
    }

    public function store(StoreExpenseRequest $request, Flatshare $flatshare): RedirectResponse
    {
        DB::transaction(function () use ($request, $flatshare) {
            Expense::create([
                'flatshare_id' => $flatshare->id,
                'category_id' => $request->input('category_id'),
                'payer_id' => $request->integer('payer_id'),
                'title' => $request->string('title'),
                'amount' => $request->input('amount'),
                'spent_at' => $request->date('spent_at'),
            ]);
        });

        return back()->with('success', 'Expense added.');
    }

    public function destroy(Flatshare $flatshare, Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);
        abort_if($expense->flatshare_id !== $flatshare->id, 404);

        $expense->delete();

        return back()->with('success', 'Expense deleted.');
    }
}
