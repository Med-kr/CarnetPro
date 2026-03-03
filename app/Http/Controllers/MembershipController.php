<?php

namespace App\Http\Controllers;

use App\Models\Flatshare;
use App\Models\Membership;
use App\Services\ReputationService;
use Illuminate\Http\RedirectResponse;

class MembershipController extends Controller
{
    public function __construct(
        protected ReputationService $reputationService
    ) {
    }

    public function leave(Flatshare $flatshare): RedirectResponse
    {
        $this->authorize('leave', $flatshare);

        $membership = $flatshare->memberships()
            ->where('user_id', auth()->id())
            ->whereNull('left_at')
            ->firstOrFail();

        $this->reputationService->handleMemberExit($membership);

        return redirect()->route('dashboard')->with('success', 'You left the flatshare.');
    }

    public function destroy(Flatshare $flatshare, Membership $membership): RedirectResponse
    {
        $this->authorize('removeMember', $flatshare);

        abort_if($membership->flatshare_id !== $flatshare->id, 404);
        abort_if($membership->role === Membership::ROLE_OWNER, 422, 'The owner cannot be removed.');
        abort_if($membership->left_at !== null, 422, 'This member already left.');

        $this->reputationService->handleMemberExit($membership, true);

        return back()->with('success', 'Member removed.');
    }
}
