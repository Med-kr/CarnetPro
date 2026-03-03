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
                                <div class="flex flex-col gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $settlement['from_user']->name }} {{ __('owes') }} {{ $settlement['to_user']->name }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500">{{ number_format($settlement['amount'], 2) }}</p>
                                    </div>

                                    @if(auth()->id() === $settlement['from_user']->id || auth()->user()->is_global_admin)
                                        <form method="POST" action="{{ route('flatshares.payments.store', $flatshare) }}" class="grid gap-3 md:grid-cols-2">
                                            @csrf
                                            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                            <input type="hidden" name="from_user_id" value="{{ $settlement['from_user']->id }}">
                                            <input type="hidden" name="to_user_id" value="{{ $settlement['to_user']->id }}">
                                            <div>
                                                <x-input-label :value="__('Amount paid')" />
                                                <x-text-input name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="number_format($settlement['amount'], 2, '.', '')" />
                                            </div>
                                            <div>
                                                <x-input-label :value="__('Method')" />
                                                <select name="method_option" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    @foreach(App\Models\Payment::methodOptions() as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <x-input-label :value="__('Custom method')" />
                                                <x-text-input name="custom_method" type="text" class="mt-1 block w-full" />
                                            </div>
                                            <div>
                                                <x-input-label :value="__('Status')" />
                                                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    @foreach(App\Models\Payment::statusOptions() as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <x-input-label :value="__('Reference')" />
                                                <x-text-input name="reference" type="text" class="mt-1 block w-full" />
                                            </div>
                                            <div>
                                                <x-input-label :value="__('Note')" />
                                                <x-text-input name="note" type="text" class="mt-1 block w-full" />
                                            </div>
                                            <div class="md:col-span-2">
                                                <x-primary-button>{{ __('Mark paid') }}</x-primary-button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No settlements required right now.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg lg:col-span-2">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Payment history') }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($flatshare->payments()->latest('paid_at')->get() as $payment)
                            <div class="rounded-md border border-gray-200 px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    {{ $payment->fromUser->name }} -> {{ $payment->toUser->name }} · {{ number_format($payment->amount, 2) }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ $payment->methodLabel() }} · {{ ucfirst($payment->status ?? 'completed') }} · {{ $payment->paid_at->format('Y-m-d H:i') }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No payments recorded yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
