<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Invitation;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): View
    {
        $invitation = null;

        if ($request->filled('invitation')) {
            $invitation = Invitation::where('token', $request->string('invitation'))
                ->with('flatshare.owner')
                ->first();
        }

        return view('auth.login', compact('invitation'));
    }

    public function store(LoginRequest $request, InvitationService $invitationService): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        if ($request->filled('invitation')) {
            $invitation = Invitation::where('token', $request->string('invitation'))
                ->with('flatshare')
                ->first();

            if (! $invitation) {
                return redirect()->route('dashboard')->withErrors([
                    'email' => 'This invitation is no longer valid.',
                ]);
            }

            try {
                $flatshare = $invitationService->accept($invitation, $request->user());
            } catch (\Illuminate\Validation\ValidationException $exception) {
                return redirect()->route('invitations.show', $invitation->token)->withErrors($exception->errors());
            }

            return redirect()->route('flatshares.show', $flatshare)->with('success', 'Invitation accepted.');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
