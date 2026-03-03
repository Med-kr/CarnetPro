<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $flatshare->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Owner') }}: {{ $flatshare->owner->name }} · {{ __('Status') }}: {{ $flatshare->status }}
                </p>
            </div>
            @can('update', $flatshare)
                <a href="{{ route('flatshares.edit', $flatshare) }}" class="inline-flex rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">
                    {{ __('Edit') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 flex flex-wrap items-center gap-3">
                    @can('cancel', $flatshare)
                        <form method="POST" action="{{ route('flatshares.cancel', $flatshare) }}">
                            @csrf
                            <x-primary-button>
                                {{ $flatshare->isActive() ? __('Deactivate') : __('Activate') }}
                            </x-primary-button>
                        </form>
                    @endcan

                    @can('delete', $flatshare)
                        <form method="POST" action="{{ route('flatshares.destroy', $flatshare) }}">
                            @csrf
                            @method('DELETE')
                            <x-danger-button>{{ __('Delete') }}</x-danger-button>
                        </form>
                    @endcan
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Members') }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($flatshare->activeMemberships as $membership)
                            <div class="rounded-md border border-gray-200 px-4 py-3">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $membership->user->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $membership->user->email }}</p>
                                    </div>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        {{ $membership->role }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No active members found.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
