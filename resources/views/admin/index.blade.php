@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-1 gap-3 min-[420px]:grid-cols-2 xl:grid-cols-4">
        <div class="cp-gradient-panel cp-stat-card rounded-[1.5rem] p-5 text-white sm:rounded-[2rem] sm:p-6">
            <p class="text-sm text-white/70">Users</p>
            <p class="mt-2 text-3xl font-semibold sm:text-4xl">{{ $stats['users'] }}</p>
        </div>
        <div class="cp-gradient-panel cp-stat-card rounded-[1.5rem] p-5 text-white sm:rounded-[2rem] sm:p-6">
            <p class="text-sm text-white/70">Flatshares</p>
            <p class="mt-2 text-3xl font-semibold sm:text-4xl">{{ $stats['flatshares'] }}</p>
        </div>
        <div class="cp-gradient-panel cp-stat-card rounded-[1.5rem] p-5 text-white sm:rounded-[2rem] sm:p-6">
            <p class="text-sm text-white/70">Expenses</p>
            <p class="mt-2 text-3xl font-semibold sm:text-4xl">{{ $stats['expenses'] }}</p>
        </div>
        <div class="cp-gradient-panel cp-stat-card rounded-[1.5rem] p-5 text-white sm:rounded-[2rem] sm:p-6">
            <p class="text-sm text-white/70">Banned</p>
            <p class="mt-2 text-3xl font-semibold sm:text-4xl">{{ $stats['banned'] }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-2 xl:gap-6">
        <section class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
            <p class="cp-kicker">Global moderation</p>
            <h2 class="mt-3 text-2xl font-semibold">Users</h2>
            <div class="cp-mobile-scroll mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="text-stone-500">
                            <th class="pb-3">Name</th>
                            <th class="pb-3">Email</th>
                            <th class="pb-3">Role</th>
                            <th class="pb-3">Status</th>
                            <th class="pb-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200/70">
                        @foreach($users as $user)
                            <tr>
                                <td class="py-3">{{ $user->name }}</td>
                                <td class="py-3">{{ $user->email }}</td>
                                <td class="py-3">{{ $user->is_global_admin ? 'Global Admin' : 'User' }}</td>
                                <td class="py-3">{{ $user->is_banned ? 'Banned' : 'Active' }}</td>
                                <td class="py-3">
                                    @if(!$user->is_global_admin)
                                        <form method="POST" action="{{ $user->is_banned ? route('admin.users.unban', $user) : route('admin.users.ban', $user) }}">
                                            @csrf
                                            <button class="rounded-full px-3 py-1 {{ $user->is_banned ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ $user->is_banned ? 'Unban' : 'Ban' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="space-y-4 sm:space-y-6">
            <div class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                <h2 class="text-xl font-semibold">Flatshares</h2>
                <div class="mt-4 space-y-3 text-sm">
                    @foreach($flatshares as $flatshare)
                        <div class="cp-panel rounded-[1.5rem] px-4 py-3">
                            {{ $flatshare->name }} · {{ $flatshare->status }} · Owner: {{ $flatshare->owner->name }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                <h2 class="text-xl font-semibold">Recent expenses</h2>
                <div class="mt-4 space-y-3 text-sm">
                    @foreach($expenses as $expense)
                        <div class="cp-panel rounded-[1.5rem] px-4 py-3">
                            {{ $expense->flatshare->name }} · {{ $expense->title }} · {{ number_format($expense->amount, 2) }} by {{ $expense->payer->name }}
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="cp-glass rounded-[1.5rem] p-4 sm:rounded-[2rem] sm:p-6">
                <h2 class="text-xl font-semibold">Monthly expenses</h2>
                <div class="mt-4 space-y-3 text-sm">
                    @forelse($monthlyExpenseStats as $line)
                        <div class="cp-panel flex items-center justify-between rounded-[1.5rem] px-4 py-3">
                            <span>{{ $line['month'] }}</span>
                            <span class="font-semibold">{{ number_format($line['amount'], 2) }}</span>
                        </div>
                    @empty
                        <p class="text-stone-500">No expense data yet.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
