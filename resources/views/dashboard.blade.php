@extends('layouts.app')

@section('content')
    <div class="grid gap-4 lg:grid-cols-[1.45fr,0.95fr] xl:gap-5">
        <section class="cp-gradient-panel cp-grid-glow rounded-[1.5rem] p-4 text-white sm:rounded-[2rem] sm:p-6 xl:p-7">
            <p class="cp-kicker !text-white/70">Overview</p>
            <h1 class="mt-3 max-w-xl text-[2rem] font-semibold leading-tight tracking-tight sm:text-[2.5rem] xl:text-[3.15rem]">Pilotez votre colocation sans perdre le fil, {{ auth()->user()->name }}.</h1>
            <p class="mt-3 max-w-xl text-sm text-white/78 sm:text-base">CarnetPro transforme vos flux de depenses en une vue claire: reputation, soldes, invitations et gouvernance admin.</p>

            <div class="mt-6 grid grid-cols-1 gap-3 min-[420px]:grid-cols-2 sm:mt-8 sm:grid-cols-3">
                <div class="cp-stat-card rounded-[1.25rem] bg-white/12 p-4 sm:rounded-[1.5rem] sm:p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-white/55">Reputation</p>
                    <p class="mt-3 text-3xl font-semibold sm:text-4xl">{{ auth()->user()->reputation }}</p>
                </div>
                <div class="cp-stat-card rounded-[1.25rem] bg-white/12 p-4 sm:rounded-[1.5rem] sm:p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-white/55">Memberships</p>
                    <p class="mt-3 text-3xl font-semibold sm:text-4xl">{{ $memberships->count() }}</p>
                </div>
                <div class="cp-stat-card rounded-[1.25rem] bg-white/12 p-4 sm:rounded-[1.5rem] sm:p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-white/55">Owned</p>
                    <p class="mt-3 text-3xl font-semibold sm:text-4xl">{{ $ownedFlatshares->count() }}</p>
                </div>
            </div>

            @if($activeMembership)
                <div class="mt-6 rounded-[1.5rem] border border-white/15 bg-white/10 p-5 sm:mt-8 sm:rounded-[1.75rem] sm:p-6">
                    <p class="text-sm text-white/65">Active flatshare</p>
                    <h2 class="mt-1 text-2xl font-semibold sm:text-3xl">{{ $activeMembership->flatshare->name }}</h2>
                    <p class="mt-2 text-sm text-white/72">Owner: {{ $activeMembership->flatshare->owner->name }}</p>
                    <a href="{{ route('flatshares.show', $activeMembership->flatshare) }}" class="cp-btn-soft mt-5 inline-flex w-full justify-center px-5 py-3 text-sm font-semibold sm:w-auto">Open flatshare</a>
                </div>
            @else
                <div class="mt-6 rounded-[1.5rem] border border-white/15 bg-white/10 p-5 sm:mt-8 sm:rounded-[1.75rem] sm:p-6">
                    <p class="text-sm text-white/68">No active flatshare.</p>
                    @can('create', App\Models\Flatshare::class)
                        <a href="{{ route('flatshares.create') }}" class="cp-btn-soft mt-5 inline-flex w-full justify-center px-5 py-3 text-sm font-semibold sm:w-auto">Create one</a>
                    @else
                        <button type="button" disabled class="cp-btn-soft mt-5 inline-flex w-full cursor-not-allowed justify-center px-5 py-3 text-sm font-semibold opacity-70 sm:w-auto">Create one</button>
                        <p class="mt-3 text-xs text-white/60">You already belong to an active flatshare.</p>
                    @endcan
                </div>
            @endif
        </section>

        <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
            <p class="cp-kicker">Navigation</p>
            <h2 class="mt-3 text-2xl font-semibold">Your flatshares</h2>
            <div class="mt-4 space-y-3">
                @forelse($memberships as $membership)
                    <a href="{{ route('flatshares.show', $membership->flatshare) }}" class="cp-panel block rounded-[1.25rem] px-4 py-4 transition hover:-translate-y-0.5 sm:rounded-[1.5rem]">
                        <div class="flex flex-col gap-3 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                            <span class="font-medium">{{ $membership->flatshare->name }}</span>
                            <span class="rounded-full bg-[#edf3ff] px-3 py-1 text-xs uppercase tracking-[0.2em] text-[#2f67d8]">{{ $membership->role }}</span>
                        </div>
                    </a>
                @empty
                    <p class="text-sm text-stone-500">No memberships yet.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
