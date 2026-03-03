@extends('layouts.app')

@section('content')
    @php
        $statusClasses = match ($invitation->status) {
            'accepted' => 'bg-emerald-100 text-emerald-700',
            'refused' => 'bg-rose-100 text-rose-700',
            'expired' => 'bg-amber-100 text-amber-700',
            default => 'bg-sky-100 text-sky-700',
        };

        $canRespond = auth()->check()
            && auth()->user()->email === $invitation->email
            && $invitation->status === \App\Models\Invitation::STATUS_PENDING
            && ! $invitation->isExpired();
    @endphp

    <section class="mx-auto max-w-4xl overflow-hidden rounded-[2rem] border border-white/60 bg-white shadow-[0_30px_80px_rgba(15,23,42,0.08)]">
        <div class="bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_42%),linear-gradient(135deg,#0f172a,#1e3a8a)] px-6 py-8 text-white sm:px-10">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-sky-200">Flatshare invitation</p>
            <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ $invitation->flatshare->name }}</h1>
                    <p class="mt-3 max-w-2xl text-sm text-slate-200 sm:text-base">
                        {{ $invitation->flatshare->owner->name }} invited <span class="font-semibold text-white">{{ $invitation->email }}</span> to join this shared space.
                    </p>
                </div>
                <span class="inline-flex w-fit rounded-full px-4 py-2 text-sm font-semibold {{ $statusClasses }}">
                    {{ ucfirst($invitation->status) }}
                </span>
            </div>
        </div>

        <div class="grid gap-6 px-6 py-8 sm:px-10 lg:grid-cols-[1.35fr_0.9fr]">
            <div>
                @if(auth()->user()?->email !== $invitation->email)
                    <div class="rounded-[1.5rem] border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                        This invitation is reserved for <strong>{{ $invitation->email }}</strong>. Switch account to respond.
                    </div>
                @elseif($invitation->status === \App\Models\Invitation::STATUS_ACCEPTED)
                    <div class="rounded-[1.5rem] border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
                        You already joined this flatshare. You can continue directly to the workspace.
                    </div>
                    <a href="{{ route('flatshares.show', $invitation->flatshare) }}" class="mt-5 inline-flex rounded-full bg-slate-900 px-5 py-3 font-medium text-white">
                        Open flatshare
                    </a>
                @elseif($invitation->status === \App\Models\Invitation::STATUS_REFUSED)
                    <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50 p-5 text-sm text-rose-900">
                        This invitation was refused. Ask the owner for a new invitation if you still want to join.
                    </div>
                @elseif($invitation->status === \App\Models\Invitation::STATUS_EXPIRED || $invitation->isExpired())
                    <div class="rounded-[1.5rem] border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                        This invitation expired on {{ $invitation->expires_at->format('d/m/Y H:i') }}.
                    </div>
                @elseif($canRespond)
                    <h2 class="text-2xl font-semibold tracking-tight text-slate-950">Ready to join?</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                        Accepting will add you to this flatshare immediately. If you are not ready yet, you can refuse now and ask for another invitation later.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                            @csrf
                            <button class="rounded-full bg-emerald-500 px-6 py-3 font-medium text-white shadow-sm transition hover:bg-emerald-600">
                                Accept invitation
                            </button>
                        </form>
                        <form method="POST" action="{{ route('invitations.refuse', $invitation->token) }}">
                            @csrf
                            <button class="rounded-full border border-rose-200 bg-white px-6 py-3 font-medium text-rose-700 transition hover:bg-rose-50">
                                Refuse
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <aside class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Invitation details</p>
                <dl class="mt-5 space-y-4 text-sm text-slate-700">
                    <div>
                        <dt class="text-slate-500">Owner</dt>
                        <dd class="mt-1 font-semibold text-slate-950">{{ $invitation->flatshare->owner->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Invited email</dt>
                        <dd class="mt-1 font-semibold text-slate-950">{{ $invitation->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Expires</dt>
                        <dd class="mt-1 font-semibold text-slate-950">{{ $invitation->expires_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </aside>
        </div>
    </section>
@endsection
