<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($activeMembership)
                        <p class="text-sm text-gray-500">{{ __('Active flatshare') }}</p>
                        <h3 class="mt-2 text-2xl font-semibold">{{ $activeMembership->flatshare->name }}</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ __('Owner') }}: {{ $activeMembership->flatshare->owner->name }}
                        </p>
                        <a href="{{ route('flatshares.show', $activeMembership->flatshare) }}" class="mt-4 inline-flex rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">
                            {{ __('Open flatshare') }}
                        </a>
                    @else
                        <p>{{ __("You don't belong to an active flatshare yet.") }}</p>
                        @can('create', App\Models\Flatshare::class)
                            <a href="{{ route('flatshares.create') }}" class="mt-4 inline-flex rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">
                                {{ __('Create flatshare') }}
                            </a>
                        @endcan
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Your memberships') }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($memberships as $membership)
                            <a href="{{ route('flatshares.show', $membership->flatshare) }}" class="block rounded-md border border-gray-200 px-4 py-3 hover:bg-gray-50">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="font-medium text-gray-900">{{ $membership->flatshare->name }}</span>
                                    <span class="text-sm text-gray-500">{{ ucfirst($membership->role) }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No memberships yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
