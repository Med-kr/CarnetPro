@extends('layouts.guest')

@section('content')
    <h2 class="text-3xl font-semibold tracking-tight">
        {{ $invitation ? 'Login to join your flatshare' : 'Connexion' }}
    </h2>
    <p class="mt-1 text-sm text-stone-500">
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

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf
        @if($invitation)
            <input type="hidden" name="invitation" value="{{ $invitation->token }}">
        @endif
        <div>
            <label class="mb-1 block text-sm font-medium" for="email">Email</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email', $invitation?->email) }}"
                @if($invitation) readonly @endif
                required
                class="cp-input"
            >
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium" for="password">Password</label>
            <input id="password" name="password" type="password" required class="cp-input">
        </div>

        <label class="flex items-center gap-2 text-sm text-stone-600">
            <input type="checkbox" name="remember" value="1">
            Remember me
        </label>

        <button class="cp-btn-primary w-full px-4 py-3 font-medium">Login</button>
    </form>

    @if(! $invitation)
        <p class="mt-4 text-sm text-stone-600">
            No account?
            <a href="{{ route('register') }}" class="font-semibold text-[#1f4fb9]">Register</a>
        </p>
    @endif
@endsection
