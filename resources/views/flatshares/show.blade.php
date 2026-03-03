@extends('layouts.app')

@section('content')
    @php($categoryIcons = \App\Models\Category::iconOptions())
    @php($paymentMethods = \App\Models\Payment::methodOptions())
    @php($paymentStatuses = \App\Models\Payment::statusOptions())
    <div class="cp-glass flex flex-col gap-4 rounded-[1.5rem] p-4 sm:gap-5 sm:rounded-[2rem] sm:p-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="cp-kicker">Flatshare space</p>
            <div class="mt-2 flex flex-col items-start gap-3 min-[420px]:flex-row min-[420px]:items-center">
                <h1 class="cp-page-title font-semibold tracking-tight">{{ $flatshare->name }}</h1>
                <span class="rounded-full px-3 py-1 text-xs uppercase tracking-[0.2em] {{ $flatshare->isActive() ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-200 text-stone-700' }}">
                    {{ $flatshare->isActive() ? 'Active' : 'Disabled' }}
                </span>
            </div>
            <p class="mt-2 text-sm text-stone-500">Owner: {{ $flatshare->owner->name }}</p>
        </div>

        <div class="flex flex-col gap-2 min-[420px]:flex-row min-[420px]:flex-wrap">
            @can('update', $flatshare)
                <a href="{{ route('flatshares.edit', $flatshare) }}" class="cp-btn-soft inline-flex justify-center px-4 py-2 text-sm">Edit</a>
                <form method="POST" action="{{ route('flatshares.cancel', $flatshare) }}">
                    @csrf
                    <button class="w-full rounded-full px-4 py-2 text-sm font-semibold min-[420px]:w-auto {{ $flatshare->isActive() ? 'bg-amber-400 text-stone-900' : 'bg-emerald-500 text-white shadow-[0_12px_26px_rgba(16,185,129,0.25)]' }}">
                        {{ $flatshare->isActive() ? 'Desactiver' : 'Activer' }}
                    </button>
                </form>
            @else
                <button
                    type="button"
                    disabled
                    class="cursor-not-allowed rounded-full bg-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 opacity-80"
                >
                    {{ $flatshare->isActive() ? 'Desactiver' : 'Activer' }}
                </button>
            @endcan
            @can('leave', $flatshare)
                <form method="POST" action="{{ route('flatshares.leave', $flatshare) }}">
                    @csrf
                    <button class="w-full rounded-full bg-rose-600 px-4 py-2 text-sm text-white min-[420px]:w-auto">Leave</button>
                </form>
            @endcan
        </div>
    </div>

    @cannot('update', $flatshare)
        <p class="mt-3 text-sm text-stone-500">Only the owner or a global admin can activate or deactivate this flatshare.</p>
    @endcannot

    <div class="cp-mobile-scroll mt-6 flex gap-3 overflow-x-auto pb-1">
        @foreach(['members' => 'Members', 'expenses' => 'Expenses', 'settlements' => 'Settlements'] as $key => $label)
            <a href="{{ route('flatshares.show', ['flatshare' => $flatshare, 'tab' => $key, 'month' => $month]) }}" class="shrink-0 rounded-full px-5 py-2.5 text-sm font-medium {{ $tab === $key ? 'bg-gradient-to-r from-[#0A2540] via-[#123E66] to-[#00ADB5] text-white shadow-[0_16px_30px_rgba(10,37,64,0.24)]' : 'cp-panel text-stone-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-[1.5fr,1fr] xl:gap-6">
        <div class="space-y-6">
            @if($tab === 'members')
                <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                    <h2 class="text-xl font-semibold">Members</h2>
                    <div class="mt-4 space-y-3">
                        @foreach($flatshare->activeMemberships as $membership)
                            <div class="cp-panel flex flex-col gap-3 rounded-[1.25rem] px-4 py-4 sm:rounded-[1.5rem] sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-medium">{{ $membership->user->name }}</p>
                                    <p class="text-sm text-stone-500">{{ $membership->user->email }} · {{ $membership->role }}</p>
                                    <p class="text-xs text-stone-500">Reputation: {{ $membership->user->reputation }}</p>
                                </div>
                                @can('removeMember', $flatshare)
                                    @if($membership->role !== 'owner')
                                        <form method="POST" action="{{ route('flatshares.memberships.destroy', [$flatshare, $membership]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-full bg-rose-600 px-4 py-2 text-sm text-white">Remove</button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($tab === 'expenses')
                <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold">Expenses</h2>
                            <p class="text-sm text-stone-500">Filter by month using YYYY-MM.</p>
                        </div>
                        <form method="GET" action="{{ route('flatshares.show', $flatshare) }}" class="flex w-full flex-col gap-2 min-[420px]:flex-row sm:w-auto">
                            <input type="hidden" name="tab" value="expenses">
                            <input name="month" value="{{ $month }}" placeholder="2026-03" class="cp-input max-w-[11rem] flex-1 px-4 py-2 text-sm">
                            <button class="cp-btn-primary px-4 py-2 text-sm">Filter</button>
                        </form>
                    </div>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        <div class="cp-panel rounded-[1.5rem] px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Total expenses</p>
                            <p class="mt-2 text-2xl font-semibold">{{ number_format($expenseStats['total_expenses'], 2) }}</p>
                        </div>
                        <div class="cp-panel rounded-[1.5rem] px-4 py-4">
                            <p class="text-xs uppercase tracking-[0.2em] text-stone-500">Entries</p>
                            <p class="mt-2 text-2xl font-semibold">{{ $expenseStats['expense_count'] }}</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3">
                        @forelse($expenses as $expense)
                            <div class="cp-panel flex flex-col gap-3 rounded-[1.25rem] px-4 py-4 sm:rounded-[1.5rem] sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-medium">{{ $expense->title }}</p>
                                    <p class="text-sm text-stone-500">
                                        {{ $expense->payer->name }} paid {{ number_format($expense->amount, 2) }} on {{ $expense->spent_at->format('Y-m-d') }}
                                        @if($expense->category)
                                            · {{ $expense->category->iconEmoji() }} {{ $expense->category->name }}
                                        @endif
                                    </p>
                                </div>
                                @can('delete', $expense)
                                    <form method="POST" action="{{ route('flatshares.expenses.destroy', [$flatshare, $expense]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-full bg-rose-600 px-4 py-2 text-sm text-white">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        @empty
                            <p class="text-sm text-stone-500">No expenses for this filter.</p>
                        @endforelse
                    </div>
                </section>
            @endif

            @if($tab === 'settlements')
                <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                    <h2 class="text-xl font-semibold">Who owes who</h2>
                    <div class="cp-flow-line cp-flow-note mt-4 rounded-[1.5rem] px-4 py-3 text-sm font-medium text-[#173a8f]">
                        Simplified transfers are generated to minimize payment hops between members.
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse($settlements as $settlement)
                            <div class="cp-panel flex flex-col gap-3 rounded-[1.25rem] px-4 py-4 sm:rounded-[1.5rem] lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <p class="font-medium">{{ $settlement['from_user']->name }} owes {{ $settlement['to_user']->name }}</p>
                                    <p class="text-sm text-stone-500">{{ number_format($settlement['amount'], 2) }}</p>
                                </div>
                                @if(auth()->id() === $settlement['from_user']->id || auth()->user()->is_global_admin)
                                    <form method="POST" action="{{ route('flatshares.payments.store', $flatshare) }}" class="grid gap-2 sm:grid-cols-2">
                                        @csrf
                                        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                        <input type="hidden" name="from_user_id" value="{{ $settlement['from_user']->id }}">
                                        <input type="hidden" name="to_user_id" value="{{ $settlement['to_user']->id }}">
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-500">Amount paid</label>
                                            <input name="amount" type="number" step="0.01" min="0.01" value="{{ number_format($settlement['amount'], 2, '.', '') }}" class="cp-input px-3 py-2 text-sm">
                                            <p class="mt-1 text-xs text-stone-500">If you pay more than {{ number_format($settlement['amount'], 2) }}, the surplus stays as account credit.</p>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-500">Method</label>
                                            <select name="method_option" class="cp-select">
                                                @foreach($paymentMethods as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-500">Custom method</label>
                                            <input name="custom_method" type="text" placeholder="PayPal, Orange Money..." class="cp-input px-3 py-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-500">Status</label>
                                            <select name="status" class="cp-select">
                                                @foreach($paymentStatuses as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-500">Reference</label>
                                            <input name="reference" type="text" placeholder="TRX-2026-001" class="cp-input px-3 py-2 text-sm">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs font-medium text-stone-500">Note</label>
                                            <input name="note" type="text" placeholder="Optional note" class="cp-input px-3 py-2 text-sm">
                                        </div>
                                        <button class="rounded-full bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-[0_12px_26px_rgba(16,185,129,0.25)] sm:col-span-2">Mark paid</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-stone-500">No remaining settlements.</p>
                        @endforelse
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6">
            <section class="cp-gradient-panel rounded-[1.5rem] p-4 text-white sm:rounded-[2rem] sm:p-6">
                <h2 class="text-xl font-semibold">Balances</h2>
                <div class="mt-4 space-y-3">
                    @foreach($balances as $line)
                        <div class="rounded-[1.5rem] border border-white/12 bg-white/10 px-4 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <p class="font-medium">{{ $line['user']->name }}</p>
                                <span class="{{ $line['balance'] >= 0 ? 'text-emerald-200' : 'text-rose-200' }}">
                                    {{ number_format($line['balance'], 2) }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-white/65">Paid {{ number_format($line['total_paid'], 2) }} · Share {{ number_format($line['share'], 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                <h2 class="text-xl font-semibold">Monthly stats</h2>
                <div class="mt-4 space-y-3 text-sm">
                    @forelse($expenseStats['monthly_totals']->take(4) as $line)
                        <div class="cp-panel flex items-center justify-between rounded-[1.5rem] px-4 py-3">
                            <span>{{ $line['month'] }}</span>
                            <span class="font-semibold">{{ number_format($line['amount'], 2) }}</span>
                        </div>
                    @empty
                        <p class="text-stone-500">No monthly data yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                <h2 class="text-xl font-semibold">By category</h2>
                <div class="mt-4 space-y-3 text-sm">
                    @forelse($expenseStats['category_totals'] as $line)
                        <div class="cp-panel flex items-center justify-between rounded-[1.5rem] px-4 py-3">
                            <span>{{ $line['category'] }}</span>
                            <span class="font-semibold">{{ number_format($line['amount'], 2) }}</span>
                        </div>
                    @empty
                        <p class="text-stone-500">No category data yet.</p>
                    @endforelse
                </div>
            </section>

            @can('invite', $flatshare)
                <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                    <h2 class="text-xl font-semibold">Invite a member</h2>
                    <form method="POST" action="{{ route('flatshares.invitations.store', $flatshare) }}" class="mt-4 space-y-3">
                        @csrf
                        <input name="email" type="email" placeholder="member@example.com" class="cp-input">
                        <button class="cp-btn-primary px-4 py-3">Send invitation</button>
                    </form>
                    @if(session('invitation_url'))
                        <div class="mt-4 rounded-[1.5rem] border border-cyan-200 bg-cyan-50 px-4 py-4 text-sm text-slate-700">
                            <p class="font-medium text-slate-900">Invitation link</p>
                            <p class="mt-1 text-slate-600">The invitation email was sent automatically. You can still open or copy this link as a fallback.</p>
                            <input readonly value="{{ session('invitation_url') }}" class="cp-input mt-3 text-sm">
                            <a href="{{ session('invitation_url') }}" class="mt-3 inline-flex rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Open link</a>
                        </div>
                    @endif
                    <div class="mt-4 space-y-2 text-sm text-stone-500">
                        @foreach($flatshare->invitations as $invitation)
                            <div class="rounded-2xl bg-[#eef4ff] px-4 py-3">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p>{{ $invitation->email }} · {{ $invitation->status }}</p>
                                        <a href="{{ route('invitations.show', $invitation->token) }}" class="mt-2 inline-flex text-xs font-semibold text-[#0A2540] underline underline-offset-4">
                                            Open invitation link
                                        </a>
                                    </div>

                                    <form method="POST" action="{{ route('flatshares.invitations.destroy', [$flatshare, $invitation]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-full bg-rose-600 px-4 py-2 text-sm text-white">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                    <h2 class="text-xl font-semibold">Categories</h2>
                    <form method="POST" action="{{ route('flatshares.categories.store', $flatshare) }}" class="mt-4 space-y-3">
                        @csrf
                        <input name="name" type="text" placeholder="Groceries" class="cp-input">
                        <div class="flex flex-wrap gap-2">
                            @foreach($categoryIcons as $key => $icon)
                                <label class="cursor-pointer">
                                    <input type="radio" name="icon" value="{{ $key }}" class="peer sr-only" @checked($loop->first)>
                                    <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/5 px-3 py-2 text-sm text-stone-700 transition peer-checked:border-cyan-400 peer-checked:bg-cyan-50 peer-checked:text-slate-950">
                                        <span>{{ $icon['emoji'] }}</span>
                                        <span>{{ $icon['label'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <button class="cp-btn-primary px-4 py-3">Add</button>
                    </form>
                    <div class="mt-4 space-y-3">
                        @foreach($flatshare->categories as $category)
                            <div class="cp-panel rounded-[1.5rem] px-4 py-3">
                                <form method="POST" action="{{ route('flatshares.categories.update', [$flatshare, $category]) }}" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <input name="name" value="{{ $category->name }}" class="cp-input px-3 py-2 text-sm">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($categoryIcons as $key => $icon)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="icon" value="{{ $key }}" class="peer sr-only" @checked($category->icon === $key)>
                                                <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/5 px-3 py-2 text-sm text-stone-700 transition peer-checked:border-cyan-400 peer-checked:bg-cyan-50 peer-checked:text-slate-950">
                                                    <span>{{ $icon['emoji'] }}</span>
                                                    <span>{{ $icon['label'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <button class="rounded-2xl bg-amber-400 px-3 py-2 text-sm font-semibold text-stone-900">Save</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('flatshares.categories.destroy', [$flatshare, $category]) }}" class="mt-2">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-rose-600">Delete</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endcan

            <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                <h2 class="text-xl font-semibold">Add expense</h2>
                <form method="POST" action="{{ route('flatshares.expenses.store', $flatshare) }}" class="mt-4 space-y-3">
                    @csrf
                    <input name="title" type="text" placeholder="Internet bill" class="cp-input">
                    <input name="amount" type="number" step="0.01" min="0.01" placeholder="45.50" class="cp-input">
                    <input name="spent_at" type="date" value="{{ now()->format('Y-m-d') }}" class="cp-input">
                    <select name="payer_id" class="cp-select">
                        @foreach($flatshare->activeMemberships as $membership)
                            <option value="{{ $membership->user_id }}">{{ $membership->user->name }}</option>
                        @endforeach
                    </select>
                    <div class="space-y-2">
                        <p class="text-sm font-medium text-stone-600">Category</p>
                        <div class="flex flex-wrap gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="category_id" value="" class="peer sr-only" checked>
                                <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/5 px-3 py-2 text-sm text-stone-700 transition peer-checked:border-cyan-400 peer-checked:bg-cyan-50 peer-checked:text-slate-950">
                                    <span>∅</span>
                                    <span>No category</span>
                                </span>
                            </label>
                            @foreach($flatshare->categories as $category)
                                <label class="cursor-pointer">
                                    <input type="radio" name="category_id" value="{{ $category->id }}" class="peer sr-only">
                                    <span class="inline-flex items-center gap-2 rounded-full border border-white/12 bg-white/5 px-3 py-2 text-sm text-stone-700 transition peer-checked:border-cyan-400 peer-checked:bg-cyan-50 peer-checked:text-slate-950">
                                        <span>{{ $category->iconEmoji() }}</span>
                                        <span>{{ $category->name }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <button class="cp-btn-primary px-4 py-3">Add expense</button>
                </form>
            </section>
        </aside>
    </div>
@endsection
