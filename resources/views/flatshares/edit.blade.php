@extends('layouts.app')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1.05fr,0.95fr]">
        <section class="cp-gradient-panel rounded-[2rem] p-7 text-white">
            <p class="cp-kicker !text-white/70">Refine workspace</p>
            <h1 class="mt-3 text-4xl font-semibold tracking-tight">Mettez a jour l’identite de votre colocation.</h1>
            <p class="mt-3 max-w-xl text-white/78">Conservez une presentation cohérente de l’espace pour les membres, les categories et les remboursements.</p>
        </section>

        <section class="cp-glass rounded-[2rem] p-6">
            <h2 class="text-2xl font-semibold">Edit flatshare</h2>

            <form method="POST" action="{{ route('flatshares.update', $flatshare) }}" class="mt-8 space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label class="mb-2 block text-sm font-medium" for="name">Flatshare name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $flatshare->name) }}" required class="cp-input">
                </div>
                <button class="cp-btn-primary px-5 py-3 font-medium">Save</button>
            </form>
        </section>
    </div>
@endsection
