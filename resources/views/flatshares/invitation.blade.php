<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Flatshare invitation') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-6">
                    <div>
                        <h3 class="text-2xl font-semibold text-gray-900">{{ $invitation->flatshare->name }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ $invitation->flatshare->owner->name }} invited <strong>{{ $invitation->email }}</strong>.
                        </p>
                    </div>

                    <div class="rounded-md border border-gray-200 px-4 py-4 text-sm text-gray-700">
                        <p><strong>{{ __('Status') }}:</strong> {{ ucfirst($invitation->status) }}</p>
                        <p class="mt-2"><strong>{{ __('Expires') }}:</strong> {{ $invitation->expires_at->format('d/m/Y H:i') }}</p>
                    </div>

                    @if(auth()->user()?->email !== $invitation->email)
                        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                            {{ __('This invitation is reserved for another email address.') }}
                        </div>
                    @elseif($invitation->status === \App\Models\Invitation::STATUS_PENDING && ! $invitation->isExpired())
                        <div class="flex flex-wrap gap-3">
                            <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                                @csrf
                                <x-primary-button>{{ __('Accept invitation') }}</x-primary-button>
                            </form>
                            <form method="POST" action="{{ route('invitations.refuse', $invitation->token) }}">
                                @csrf
                                <x-danger-button>{{ __('Refuse') }}</x-danger-button>
                            </form>
                        </div>
                    @elseif($invitation->status === \App\Models\Invitation::STATUS_ACCEPTED)
                        <a href="{{ route('flatshares.show', $invitation->flatshare) }}" class="inline-flex rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">
                            {{ __('Open flatshare') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
