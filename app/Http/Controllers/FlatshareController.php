<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlatshareRequest;
use App\Http\Requests\UpdateFlatshareRequest;
use App\Models\Category;
use App\Models\Flatshare;
use App\Models\Membership;
use App\Services\SettlementService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FlatshareController extends Controller
{
    public function __construct(
        protected SettlementService $settlementService
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $flatshares = Flatshare::query()
            ->whereHas('memberships', fn ($query) => $query->where('user_id', $user->id))
            ->with('owner')
            ->latest()
            ->get();

        $activeFlatshare = $flatshares->first(function (Flatshare $flatshare) use ($user) {
            if ($flatshare->status !== Flatshare::STATUS_ACTIVE) {
                return false;
            }

            return $flatshare->memberships()
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->exists();
        });

        $pastFlatshares = $flatshares->reject(fn (Flatshare $flatshare) => $activeFlatshare?->id === $flatshare->id)
            ->values();

        return view('flatshares.index', compact('flatshares', 'activeFlatshare', 'pastFlatshares'));
    }

    public function create(): View
    {
        $this->authorize('create', Flatshare::class);

        return view('flatshares.create');
    }

    public function store(StoreFlatshareRequest $request): RedirectResponse
    {
        $flatshare = DB::transaction(function () use ($request) {
            $flatshare = Flatshare::create([
                'name' => $request->string('name'),
                'owner_id' => $request->user()->id,
                'status' => Flatshare::STATUS_CANCELLED,
            ]);

            Membership::create([
                'user_id' => $request->user()->id,
                'flatshare_id' => $flatshare->id,
                'role' => Membership::ROLE_OWNER,
                'joined_at' => now(),
            ]);

            Category::ensureDefaultsForFlatshare($flatshare->id);

            return $flatshare;
        });

        return redirect()->route('flatshares.show', $flatshare)->with('success', 'Flatshare created in deactivated mode. Activate it when you are ready.');
    }

    public function show(Request $request, Flatshare $flatshare): View|RedirectResponse
    {
        if ($redirect = $this->redirectIfCannotView($request, $flatshare)) {
            return $redirect;
        }

        $month = $request->string('month')->toString() ?: null;
        $tab = $request->string('tab')->toString() ?: 'members';
        $overview = $this->settlementService->buildOverview($flatshare, $month);

        return view('flatshares.show', [
            'flatshare' => $flatshare->load([
                'owner',
                'activeMemberships.user',
                'categories',
                'invitations' => fn ($query) => $query->latest(),
                'payments.fromUser',
                'payments.toUser',
                'adjustments.fromUser',
                'adjustments.toUser',
            ]),
            'month' => $month,
            'tab' => $tab,
            ...$overview,
        ]);
    }

    public function edit(Flatshare $flatshare): View
    {
        $this->authorize('update', $flatshare);

        return view('flatshares.edit', compact('flatshare'));
    }

    public function update(UpdateFlatshareRequest $request, Flatshare $flatshare): RedirectResponse
    {
        $flatshare->update($request->validated());

        return redirect()->route('flatshares.show', $flatshare)->with('success', 'Flatshare updated.');
    }

    public function cancel(Flatshare $flatshare): RedirectResponse
    {
        $this->authorize('cancel', $flatshare);

        $nextStatus = $flatshare->isActive()
            ? Flatshare::STATUS_CANCELLED
            : Flatshare::STATUS_ACTIVE;

        if ($nextStatus === Flatshare::STATUS_ACTIVE) {
            $conflictingMemberships = $flatshare->activeMemberships()
                ->with('user')
                ->get()
                ->filter(function (Membership $membership) use ($flatshare) {
                    if ($membership->user?->is_global_admin) {
                        return false;
                    }

                    return $membership->user?->memberships()
                        ->whereNull('left_at')
                        ->where('flatshare_id', '!=', $flatshare->id)
                        ->whereHas('flatshare', fn ($query) => $query->where('status', Flatshare::STATUS_ACTIVE))
                        ->exists();
                });

            if ($conflictingMemberships->isNotEmpty()) {
                return back()->withErrors([
                    'status' => 'Flatshare cannot be activated because some members already belong to another active flatshare: '.$conflictingMemberships->pluck('user.name')->join(', '),
                ]);
            }
        }

        if ($nextStatus === Flatshare::STATUS_CANCELLED) {
            app(\App\Services\ReputationService::class)->handleFlatshareCancellation($flatshare);
        }

        $flatshare->update(['status' => $nextStatus]);

        return back()->with('success', $nextStatus === Flatshare::STATUS_ACTIVE
            ? 'Flatshare activated.'
            : 'Flatshare deactivated.');
    }

    public function destroy(Flatshare $flatshare): RedirectResponse
    {
        $this->authorize('delete', $flatshare);

        $flatshare->delete();

        return redirect()->route('flatshares.index')->with('success', 'Flatshare deleted.');
    }

    public function redirectIfCannotView(Request $request, Flatshare $flatshare): ?RedirectResponse
    {
        try {
            $this->authorize('view', $flatshare);

            return null;
        } catch (AuthorizationException) {
            return redirect()
                ->route('flatshares.index')
                ->with('warning', 'You no longer have access to this flatshare.');
        }
    }
}
