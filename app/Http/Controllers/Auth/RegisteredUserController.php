<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Invitation;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(Request $request): View
    {
        $invitation = null;

        if ($request->filled('invitation')) {
            $invitation = Invitation::where('token', $request->string('invitation'))
                ->with('flatshare.owner')
                ->first();
        }

        return view('auth.register', compact('invitation'));
    }

    public function store(RegisterRequest $request, InvitationService $invitationService): RedirectResponse
    {
        $user = DB::transaction(function () use ($request) {
            return User::create([
                'name' => $request->string('name')->toString(),
                'email' => $request->string('email')->toString(),
                'password' => $request->string('password')->toString(),
                'is_global_admin' => User::count() === 0,
            ]);
        });

        Auth::login($user);

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
                $flatshare = $invitationService->accept($invitation, $user);
            } catch (\Illuminate\Validation\ValidationException $exception) {
                return redirect()->route('invitations.show', $invitation->token)->withErrors($exception->errors());
            }

            return redirect()->route('flatshares.show', $flatshare)->with('success', 'Invitation accepted.');
        }

        return redirect()->route('dashboard')->with('success', 'Welcome to CarnetPro.');
    }
}
