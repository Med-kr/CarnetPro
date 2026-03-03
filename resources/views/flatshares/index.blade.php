@extends('layouts.app')

@section('content')
    @php
        $badgeClasses = fn (string $type) => match ($type) {
            'active' => 'bg-emerald-100 text-emerald-700',
            'left' => 'bg-amber-100 text-amber-700',
            default => 'bg-slate-200 text-slate-700',
        };
    @endphp

    <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="cp-kicker">Workspace browser</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">Vos colocations, dans une vue propre et rapide.</h1>
                <p class="mt-2 max-w-2xl text-sm text-stone-500">Retrouvez vos espaces actifs, leur statut et le proprietaire sans passer par plusieurs pages.</p>
            </div>
            @can('create', App\Models\Flatshare::class)
                <a href="{{ route('flatshares.create') }}" class="cp-btn-primary inline-flex w-full justify-center px-5 py-3 font-medium sm:w-auto">New colocation</a>
            @else
                <div class="w-full sm:w-auto sm:text-right">
                    <button type="button" disabled class="w-full cursor-not-allowed rounded-full bg-slate-300 px-5 py-3 font-medium text-slate-600 opacity-80 sm:w-auto">
                        New colocation
                    </button>
                    <p class="mt-2 text-xs text-stone-500">Leave or deactivate your active flatshare before creating another one.</p>
                </div>
            @endcan
        </div>
    </section>

    <div class="mt-6 space-y-6">
        <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
            <div class="flex flex-col gap-3 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                <div>
                    <p class="cp-kicker">Current</p>
                    <h2 class="mt-2 text-2xl font-semibold">Active flatshare</h2>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] {{ $badgeClasses($activeFlatshare ? 'active' : 'cancelled') }}">
                    {{ $activeFlatshare ? 'Active' : 'None' }}
                </span>
            </div>

            @if($activeFlatshare)
                <a href="{{ route('flatshares.show', $activeFlatshare) }}" class="mt-5 block rounded-[1.5rem] border border-emerald-200/60 bg-emerald-50/70 p-4 transition hover:-translate-y-1 sm:rounded-[1.75rem] sm:p-5">
                    <div class="flex flex-col gap-3 min-[420px]:flex-row min-[420px]:items-start min-[420px]:justify-between">
                        <div>
                            <p class="cp-kicker !text-emerald-700">Active flatshare</p>
                            <h3 class="mt-2 text-2xl font-semibold text-slate-950">{{ $activeFlatshare->name }}</h3>
                        </div>
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs uppercase tracking-[0.2em] text-emerald-700">Active</span>
                    </div>
                    <div class="mt-6 space-y-2 text-sm text-slate-600">
                        <p>Owner: <span class="font-medium text-slate-900">{{ $activeFlatshare->owner->name }}</span></p>
                        <p>This is the flatshare where your live balances, expenses and settlements are active.</p>
                    </div>
                </a>
            @else
                <div class="mt-5 rounded-[1.75rem] border border-dashed border-slate-300 p-5 text-sm text-stone-500">
                    You do not currently belong to an active flatshare.
                </div>
            @endif
        </section>

        @if($pastFlatshares->isNotEmpty())
            <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                <div class="flex flex-col gap-3 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                    <div>
                        <p class="cp-kicker">Archive</p>
                        <h2 class="mt-2 text-2xl font-semibold">Past flatshares</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs uppercase tracking-[0.2em] text-slate-600">{{ $pastFlatshares->count() }}</span>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($pastFlatshares as $flatshare)
                        @php
                            $membership = $flatshare->memberships()->where('user_id', auth()->id())->latest('id')->first();
                            $state = $membership?->left_at ? 'left' : ($flatshare->status === \App\Models\Flatshare::STATUS_CANCELLED ? 'cancelled' : 'inactive');
                            $stateLabel = $membership?->left_at ? 'Left' : ($flatshare->status === \App\Models\Flatshare::STATUS_CANCELLED ? 'Cancelled' : 'Inactive');
                        @endphp

                        <a href="{{ route('flatshares.show', $flatshare) }}" class="cp-panel block rounded-[1.5rem] p-4 transition hover:-translate-y-1 sm:rounded-[2rem] sm:p-5">
                            <div class="flex flex-col gap-3 min-[420px]:flex-row min-[420px]:items-start min-[420px]:justify-between">
                                <div>
                                    <p class="cp-kicker">Flatshare</p>
                                    <h3 class="mt-2 text-2xl font-semibold">{{ $flatshare->name }}</h3>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs uppercase tracking-[0.2em] {{ $badgeClasses($state) }}">{{ $stateLabel }}</span>
                            </div>
                            <div class="mt-8 space-y-2 text-sm text-stone-500">
                                <p>Owner: <span class="font-medium text-stone-700">{{ $flatshare->owner->name }}</span></p>
                                <p>Status: {{ ucfirst($flatshare->status) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
