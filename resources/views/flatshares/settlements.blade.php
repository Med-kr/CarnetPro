<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Settlements') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $flatshare->name }}</p>
            </div>
            <a href="{{ route('flatshares.show', $flatshare) }}" class="inline-flex rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">
                {{ __('Back to flatshare') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Balances') }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($balances as $line)
                            <div class="rounded-md border border-gray-200 px-4 py-3">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $line['user']->name }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ __('Paid') }} {{ number_format($line['total_paid'], 2) }} ·
                                            {{ __('Share') }} {{ number_format($line['share'], 2) }}
                                        </p>
                                    </div>
                                    <span class="font-semibold {{ $line['balance'] >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ number_format($line['balance'], 2) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No balances available yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Who owes who') }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($settlements as $settlement)
                            <div class="rounded-md border border-gray-200 px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    {{ $settlement['from_user']->name }} {{ __('owes') }} {{ $settlement['to_user']->name }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">{{ number_format($settlement['amount'], 2) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No settlements required right now.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
