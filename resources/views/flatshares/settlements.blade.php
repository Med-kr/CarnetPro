@extends('layouts.app')

@section('content')
    @php($paymentMethods = \App\Models\Payment::methodOptions())
    @php($paymentStatuses = \App\Models\Payment::statusOptions())
    <div class="grid gap-6 lg:grid-cols-[1.2fr,0.8fr]">
        <section class="cp-glass rounded-[2rem] p-6">
            <p class="cp-kicker">Settlement map</p>
            <h1 class="mt-2 text-4xl font-semibold tracking-tight">Qui doit qui dans {{ $flatshare->name }} ?</h1>
            <div class="cp-flow-line mt-6 rounded-[2rem] p-6">
                <div class="grid gap-4">
                    @forelse($settlements as $settlement)
                        <div class="cp-panel rounded-[1.5rem] px-5 py-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div>
                                    <div class="text-lg font-semibold text-stone-800">{{ $settlement['from_user']->name }}</div>
                                    <div class="text-xs text-stone-500">{{ $settlement['from_user']->name }} owes {{ $settlement['to_user']->name }}</div>
                                </div>
                                <div class="rounded-full bg-[#edf3ff] px-4 py-2 text-sm font-medium text-[#2f67d8]">doit</div>
                                <div class="text-lg font-semibold text-stone-800">{{ $settlement['to_user']->name }}</div>
                                <div class="text-2xl font-semibold text-[#173a8f]">{{ number_format($settlement['amount'], 2) }}</div>
                            </div>

                            @if(auth()->id() === $settlement['from_user']->id || auth()->user()->is_global_admin)
                                <form method="POST" action="{{ route('flatshares.payments.store', $flatshare) }}" class="mt-4 grid gap-3 md:grid-cols-2">
                                    @csrf
                                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                                    <input type="hidden" name="from_user_id" value="{{ $settlement['from_user']->id }}">
                                    <input type="hidden" name="to_user_id" value="{{ $settlement['to_user']->id }}">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-stone-500">Amount paid</label>
                                        <input name="amount" type="number" step="0.01" min="0.01" value="{{ number_format($settlement['amount'], 2, '.', '') }}" class="cp-input">
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
                                        <input name="custom_method" type="text" placeholder="PayPal, Orange Money..." class="cp-input">
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
                                        <input name="reference" type="text" placeholder="TRX-2026-001" class="cp-input">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-stone-500">Note</label>
                                        <input name="note" type="text" placeholder="Optional note" class="cp-input">
                                    </div>
                                    <button class="rounded-full bg-emerald-500 px-4 py-3 text-sm font-semibold text-white shadow-[0_12px_26px_rgba(16,185,129,0.25)] md:col-span-2">
                                        Mark paid
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <div class="cp-panel rounded-[1.5rem] px-5 py-4 text-sm text-stone-500">Everything is balanced.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="space-y-6">
            <div class="cp-gradient-panel rounded-[2rem] p-6 text-white">
                <h2 class="text-2xl font-semibold">Balances</h2>
                <div class="mt-4 space-y-3">
                    @foreach($balances as $line)
                        <div class="rounded-[1.5rem] border border-white/10 bg-white/10 px-4 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <span>{{ $line['user']->name }}</span>
                                <span>{{ number_format($line['balance'], 2) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="cp-glass rounded-[2rem] p-6">
                <h2 class="text-xl font-semibold">Read model</h2>
                <p class="mt-3 text-sm text-stone-500">Les remboursements affiches sont simplifies pour reduire le nombre total de transferts entre membres.</p>
            </div>

            <div class="cp-glass rounded-[2rem] p-6">
                <h2 class="text-xl font-semibold">Recent payments</h2>
                <div class="mt-4 space-y-3 text-sm">
                    @forelse($flatshare->payments->sortByDesc('paid_at')->take(5) as $payment)
                        <div class="cp-panel rounded-[1.5rem] px-4 py-3">
                            <div class="flex items-center justify-between gap-4">
                                <span class="font-medium text-stone-800">{{ $payment->fromUser->name }} -> {{ $payment->toUser->name }}</span>
                                <span class="font-semibold text-[#173a8f]">{{ number_format($payment->amount, 2) }}</span>
                            </div>
                            <p class="mt-1 text-xs text-stone-500">
                                {{ $payment->methodLabel() }} · {{ \App\Models\Payment::statusOptions()[$payment->status] ?? ucfirst((string) $payment->status) }} · {{ $payment->paid_at?->format('d/m/Y H:i') }}
                            </p>
                            @if($payment->reference)
                                <p class="mt-1 text-xs text-stone-500">Ref: {{ $payment->reference }}</p>
                            @endif
                            @if((float) ($payment->credit_amount ?? 0) > 0)
                                <p class="mt-1 text-xs text-emerald-600">
                                    Applied to debt: {{ number_format((float) $payment->applied_amount, 2) }} · Credit kept: {{ number_format((float) $payment->credit_amount, 2) }}
                                </p>
                            @endif
                            @if($payment->note)
                                <p class="mt-1 text-xs text-stone-500">{{ $payment->note }}</p>
                            @endif
                        </div>
                    @empty
                        <div class="cp-panel rounded-[1.5rem] px-4 py-3 text-stone-500">No payments recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
