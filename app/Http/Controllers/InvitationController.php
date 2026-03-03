<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvitationRequest;
use App\Models\Flatshare;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function __construct(
        protected InvitationService $invitationService
    ) {
    }

    public function store(StoreInvitationRequest $request, Flatshare $flatshare): RedirectResponse
    {
        if (! $flatshare->isActive()) {
            return back()->withErrors(['email' => 'Cannot invite into a cancelled flatshare.']);
        }

        if (
            ! $request->user()?->is_global_admin &&
            Membership::query()
                ->whereNull('left_at')
                ->whereHas('user', fn ($query) => $query->where('email', $request->string('email')))
                ->whereHas('flatshare', fn ($query) => $query->where('status', Flatshare::STATUS_ACTIVE))
                ->exists()
        ) {
            return back()->withErrors(['email' => 'This user already has an active flatshare.']);
        }

        $result = $this->invitationService->create($flatshare, $request->string('email')->toString());
        $invitation = $result['invitation'];
        $invitationUrl = route('invitations.show', $invitation->token);

        if (! $result['mail_sent']) {
            return back()
                ->with('warning', 'Invitation created, but the email could not be sent. Use the fallback link below.')
                ->with('invitation_url', $invitationUrl);
        }

        return back()
            ->with('status', "Invitation email sent to {$invitation->email}.")
            ->with('invitation_url', $invitationUrl);
    }

    public function destroy(Flatshare $flatshare, Invitation $invitation): RedirectResponse
    {
        $this->authorize('invite', $flatshare);

        abort_unless($invitation->flatshare_id === $flatshare->id, 404);

        $invitation->delete();

        return back()->with('status', 'Invitation removed.');
    }

    public function show(Request $request, string $token): View|RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->with('flatshare.owner')->firstOrFail();

        if ($invitation->isExpired() && $invitation->status === Invitation::STATUS_PENDING) {
            $invitation->update(['status' => Invitation::STATUS_EXPIRED]);
        }

        if (! $request->user()) {
            $authRoute = User::where('email', $invitation->email)->exists() ? 'login' : 'register';

            return redirect()->route($authRoute, ['invitation' => $invitation->token]);
        }

        return view('flatshares.invitation', compact('invitation'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->with('flatshare')->firstOrFail();
        $user = $request->user();

        abort_unless($user, 403);

        try {
            $flatshare = $this->invitationService->accept($invitation, $user);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('flatshares.show', $flatshare)->with('status', 'Invitation accepted.');
    }

    public function refuse(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();
        $user = $request->user();

        abort_unless($user, 403);

        if ($user->email !== $invitation->email) {
            return back()->withErrors(['email' => 'This invitation is not for your email address.']);
        }

        $invitation->update(['status' => Invitation::STATUS_REFUSED]);

        return redirect()->route('dashboard')->with('status', 'Invitation refused.');
    }
}
