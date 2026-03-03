<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Global admin dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">{{ __('Users') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['users'] }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">{{ __('Flatshares') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['flatshares'] }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">{{ __('Expenses') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['expenses'] }}</p>
                </div>
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-500">{{ __('Banned') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['banned'] }}</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Users') }}</h3>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead>
                                <tr class="text-gray-500">
                                    <th class="pb-3">{{ __('Name') }}</th>
                                    <th class="pb-3">{{ __('Email') }}</th>
                                    <th class="pb-3">{{ __('Role') }}</th>
                                    <th class="pb-3">{{ __('Status') }}</th>
                                    <th class="pb-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($users as $user)
                                    <tr>
                                        <td class="py-3">{{ $user->name }}</td>
                                        <td class="py-3">{{ $user->email }}</td>
                                        <td class="py-3">{{ $user->is_global_admin ? __('Global admin') : __('User') }}</td>
                                        <td class="py-3">{{ $user->is_banned ? __('Banned') : __('Active') }}</td>
                                        <td class="py-3">
                                            @if(! $user->is_global_admin)
                                                <form method="POST" action="{{ $user->is_banned ? route('admin.users.unban', $user) : route('admin.users.ban', $user) }}">
                                                    @csrf
                                                    <button class="rounded-md px-3 py-1 text-sm font-semibold {{ $user->is_banned ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                        {{ $user->is_banned ? __('Unban') : __('Ban') }}
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Flatshares') }}</h3>
                        <div class="mt-4 space-y-3 text-sm">
                            @foreach($flatshares as $flatshare)
                                <div class="rounded-md border border-gray-200 px-4 py-3">
                                    {{ $flatshare->name }} · {{ $flatshare->status }} · {{ __('Owner') }}: {{ $flatshare->owner->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Recent expenses') }}</h3>
                        <div class="mt-4 space-y-3 text-sm">
                            @foreach($expenses as $expense)
                                <div class="rounded-md border border-gray-200 px-4 py-3">
                                    {{ $expense->flatshare->name }} · {{ $expense->title }} · {{ number_format($expense->amount, 2) }} · {{ $expense->payer->name }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Monthly expenses') }}</h3>
                        <div class="mt-4 space-y-3 text-sm">
                            @forelse($monthlyExpenseStats as $line)
                                <div class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3">
                                    <span>{{ $line['month'] }}</span>
                                    <span class="font-semibold">{{ number_format($line['amount'], 2) }}</span>
                                </div>
                            @empty
                                <p class="text-gray-500">{{ __('No expense data yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
