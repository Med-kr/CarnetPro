@extends('layouts.app')

@section('content')
    <div class="grid gap-4 lg:grid-cols-[1.05fr,0.95fr] xl:gap-6">
        <section class="cp-gradient-panel cp-grid-glow rounded-[1.5rem] p-5 text-white sm:rounded-[2rem] sm:p-7">
            <p class="cp-kicker !text-white/70">Account space</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight sm:text-4xl">Votre profil CarnetPro.</h1>
            <p class="mt-3 max-w-xl text-white/78">Mettez a jour votre identite, vos acces et vos donnees de securite avec la meme interface que le reste de l’application.</p>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-[1.5rem] border border-white/15 bg-white/10 p-5 sm:rounded-[1.75rem]">
                    <p class="text-sm text-white/65">Connected account</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $user->name }}</p>
                    <p class="text-sm text-white/75">{{ $user->email }}</p>
                </div>
                <div class="rounded-[1.5rem] border border-white/15 bg-white/10 p-5 sm:rounded-[1.75rem]">
                    <p class="text-sm text-white/65">Account status</p>
                    <p class="mt-2 text-2xl font-semibold">{{ $user->is_global_admin ? 'Global admin' : 'Member' }}</p>
                    <p class="text-sm text-white/75">Reputation: {{ $user->reputation }}</p>
                </div>
            </div>
        </section>

        <div class="space-y-6">
            <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                <div class="flex flex-col gap-3 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold">Profile</h2>
                        <p class="mt-1 text-sm text-stone-500">Gardez vos informations d’identite a jour.</p>
                    </div>
                    <span class="rounded-full bg-[#edf3ff] px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-[#2f67d8]">Identity</span>
                </div>
                <form method="POST" action="{{ route('profile.update') }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="mb-2 block text-sm font-medium" for="name">Name</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="cp-input">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="cp-input">
                    </div>
                    <button class="cp-btn-primary px-5 py-3 font-medium">Save profile</button>
                </form>
            </section>

            <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                <div class="flex flex-col gap-3 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">Password</h2>
                        <p class="mt-1 text-sm text-stone-500">Mettez a jour vos acces et gerez la suppression du compte.</p>
                    </div>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium uppercase tracking-[0.2em] text-amber-700">Security</span>
                </div>
                <form method="POST" action="{{ route('profile.password') }}" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <input name="current_password" type="password" placeholder="Current password" class="cp-input">
                    <input name="password" type="password" placeholder="New password" class="cp-input">
                    <input name="password_confirmation" type="password" placeholder="Confirm new password" class="cp-input">
                    <button class="rounded-full bg-amber-500 px-5 py-3 font-medium text-stone-900">Update password</button>
                </form>

                <form method="POST" action="{{ route('profile.destroy') }}" class="mt-8 space-y-4 border-t border-stone-200 pt-6">
                    @csrf
                    @method('DELETE')
                    <input name="password" type="password" placeholder="Confirm password to delete account" class="cp-input">
                    <button class="rounded-full bg-rose-600 px-5 py-3 font-medium text-white">Delete account</button>
                </form>
            </section>
        </div>
    </div>
@endsection
