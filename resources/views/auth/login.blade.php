@extends('layouts.guest')

@section('content')
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="cp-kicker">Access</p>
            <h2 class="mt-2 text-3xl font-semibold tracking-tight">
                {{ $invitation ? 'Login to join your flatshare' : 'Connexion' }}
            </h2>
        </div>
        <div class="hidden rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-sky-800 sm:block">
            Secure
        </div>
    </div>
    <p class="mt-2 text-sm text-stone-500">
        {{ $invitation ? 'Use the invited email to join the flatshare immediately after login.' : 'Accedez a votre espace CarnetPro.' }}
    </p>

    @if($invitation)
        <div class="mt-6 rounded-[1.5rem] border border-sky-100 bg-sky-50/80 p-5 text-sm text-slate-700">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-700">Invitation</p>
            <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ $invitation->flatshare->name }}</h3>
            <p class="mt-2">Owner: {{ $invitation->flatshare->owner->name }}</p>
            <p class="mt-1">Invited email: {{ $invitation->email }}</p>
            <p class="mt-1">Expires: {{ $invitation->expires_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="mt-4 rounded-[1.5rem] border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            <p class="font-semibold">This email already has an account</p>
            <p class="mt-2 leading-6">
                Log in with the invited email to join immediately. If you never created a password for this email, use the register path below instead of retrying login.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
        @csrf
        @if($invitation)
            <input type="hidden" name="invitation" value="{{ $invitation->token }}">
        @endif

        <div class="space-y-4 rounded-[1.5rem] border border-slate-200/80 bg-white/80 p-4 shadow-[0_18px_45px_rgba(15,23,42,0.06)]">
            <div>
                <label class="mb-2 block text-sm font-medium text-slate-700" for="email">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email', $invitation?->email) }}"
                    @if($invitation) readonly @endif
                    required
                    class="cp-input cp-input-lg"
                    placeholder="vous@exemple.com"
                >
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label class="block text-sm font-medium text-slate-700" for="password">Password</label>
                    <span class="text-xs text-stone-400">Minimum security required</span>
                </div>
                <input id="password" name="password" type="password" required class="cp-input cp-input-lg" placeholder="Votre mot de passe">
            </div>
        </div>

        <label class="flex items-center gap-3 rounded-full border border-slate-200 bg-slate-50/80 px-4 py-3 text-sm text-stone-600">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
            <span>Remember me on this device</span>
        </label>

        <button class="cp-btn-primary w-full px-4 py-3.5 font-medium">Login</button>
    </form>

    @if(! $invitation)
        <p class="mt-5 text-sm text-stone-600">
            No account?
            <a href="{{ route('register') }}" class="font-semibold text-[#1f4fb9]">Register</a>
        </p>
    @endif
@endsection
