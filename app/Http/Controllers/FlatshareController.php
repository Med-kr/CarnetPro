<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFlatshareRequest;
use App\Http\Requests\UpdateFlatshareRequest;
use App\Models\Category;
use App\Models\Flatshare;
use App\Models\Membership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FlatshareController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $flatshares = Flatshare::query()
            ->whereHas('memberships', fn ($query) => $query->where('user_id', $user->id))
            ->with(['owner', 'memberships'])
            ->latest()
            ->get();

        $activeFlatshare = $flatshares->first(function (Flatshare $flatshare) use ($user) {
            if (! $flatshare->isActive()) {
                return false;
            }

            return $flatshare->memberships
                ->where('user_id', $user->id)
                ->where('left_at', null)
                ->isNotEmpty();
        });

        $archivedFlatshares = $flatshares
            ->reject(fn (Flatshare $flatshare) => $activeFlatshare?->id === $flatshare->id)
            ->values();

        return view('flatshares.index', compact('activeFlatshare', 'archivedFlatshares'));
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
                'name' => $request->string('name')->toString(),
                'status' => Flatshare::STATUS_CANCELLED,
                'owner_id' => $request->user()->id,
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

        return redirect()
            ->route('flatshares.show', $flatshare)
            ->with('status', 'Flatshare created. Activate it when you are ready.');
    }

    public function show(Flatshare $flatshare): View
    {
        $this->authorize('view', $flatshare);

        $flatshare->load(['owner', 'activeMemberships.user', 'categories']);

        return view('flatshares.show', compact('flatshare'));
    }

    public function edit(Flatshare $flatshare): View
    {
        $this->authorize('update', $flatshare);

        return view('flatshares.edit', compact('flatshare'));
    }

    public function update(UpdateFlatshareRequest $request, Flatshare $flatshare): RedirectResponse
    {
        $flatshare->update($request->validated());

        return redirect()
            ->route('flatshares.show', $flatshare)
            ->with('status', 'Flatshare updated.');
    }

    public function cancel(Flatshare $flatshare): RedirectResponse
    {
        $this->authorize('cancel', $flatshare);

        $activeAfterUpdate = ! $flatshare->isActive();

        $flatshare->update([
            'status' => $activeAfterUpdate
                ? Flatshare::STATUS_ACTIVE
                : Flatshare::STATUS_CANCELLED,
        ]);

        return back()->with(
            'status',
            $activeAfterUpdate ? 'Flatshare activated.' : 'Flatshare deactivated.'
        );
    }

    public function destroy(Flatshare $flatshare): RedirectResponse
    {
        $this->authorize('delete', $flatshare);

        $flatshare->delete();

        return redirect()
            ->route('flatshares.index')
            ->with('status', 'Flatshare deleted.');
    }
}
