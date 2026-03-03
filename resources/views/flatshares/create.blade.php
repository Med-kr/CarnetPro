@extends('layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.05fr,0.95fr]">
        <section class="cp-gradient-panel cp-grid-glow rounded-[2rem] p-7 text-white">
            <p class="cp-kicker !text-white/70">Create flow</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight">Lancez une nouvelle colocation avec une base claire.</h1>
            <p class="mt-3 max-w-xl text-white/78">Un seul espace actif par utilisateur. Creez-le une fois, puis gerez invitations, depenses et remboursements dans le meme cockpit.</p>
        </section>

        <section class="cp-glass rounded-[2rem] p-6">
            <h2 class="text-2xl font-semibold">Create flatshare</h2>
            <p class="mt-2 text-sm text-stone-500">A user can only have one active flatshare at a time.</p>

            <form method="POST" action="{{ route('flatshares.store') }}" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium" for="name">Flatshare name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required class="cp-input">
                </div>
                <button class="cp-btn-primary px-5 py-3 font-medium">Create</button>
            </form>
        </section>
    </div>
@endsection
