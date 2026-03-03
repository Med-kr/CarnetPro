@extends('layouts.guest')

@section('content')
    <h2 class="text-3xl font-semibold tracking-tight">
        {{ $invitation ? 'Create your account and join the flatshare' : 'Inscription' }}
    </h2>
    <p class="mt-1 text-sm text-stone-500">
        {{ $invitation ? 'Your account will be connected to this invitation right after registration.' : 'Le premier utilisateur inscrit devient admin global.' }}
    </p>

    @if($invitation)
        <div class="mt-6 rounded-[1.5rem] border border-emerald-100 bg-emerald-50/80 p-5 text-sm text-slate-700">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-emerald-700">Invitation</p>
            <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ $invitation->flatshare->name }}</h3>
            <p class="mt-2">Owner: {{ $invitation->flatshare->owner->name }}</p>
            <p class="mt-1">Invited email: {{ $invitation->email }}</p>
            <p class="mt-1">Expires: {{ $invitation->expires_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="mt-4 rounded-[1.5rem] border border-emerald-200 bg-white p-5 text-sm text-slate-700">
            <p class="font-semibold text-slate-900">Create account with this invited email</p>
            <p class="mt-2 leading-6 text-slate-600">
                Once registration is complete, you will be added directly to <strong>{{ $invitation->flatshare->name }}</strong>.
            </p>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf
        @if($invitation)
            <input type="hidden" name="invitation" value="{{ $invitation->token }}">
        @endif
        <div>
            <label class="mb-1 block text-sm font-medium" for="name">Name</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required class="cp-input">
        </div>

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

        <div>
            <label class="mb-1 block text-sm font-medium" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="cp-input">
        </div>

        <button class="cp-btn-primary w-full px-4 py-3 font-medium">Create account</button>
    </form>

    @if($invitation)
        <div class="mt-6 rounded-[1.5rem] border border-sky-200 bg-sky-50 p-5">
            <p class="text-sm font-semibold text-slate-900">Already have a password for this email?</p>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Use login instead and the invitation will be accepted right after authentication.
            </p>
            <a
                href="{{ route('login', ['invitation' => $invitation->token]) }}"
                class="mt-4 inline-flex w-full items-center justify-center rounded-full border border-sky-300 bg-white px-4 py-3 text-sm font-semibold text-sky-800"
            >
                Login with this invitation
            </a>
        </div>
    @else
        <p class="mt-4 text-sm text-stone-600">
            Already registered?
            <a href="{{ route('login') }}" class="font-semibold text-[#1f4fb9]">Login</a>
        </p>
    @endif
@endsection
