<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Flatshares') }}</h2>
            @can('create', App\Models\Flatshare::class)
                <a href="{{ route('flatshares.create') }}" class="inline-flex rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">
                    {{ __('New flatshare') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Active flatshare') }}</h3>
                    @if($activeFlatshare)
                        <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $activeFlatshare->name }}</p>
                                    <p class="text-sm text-gray-600">{{ __('Owner') }}: {{ $activeFlatshare->owner->name }}</p>
                                </div>
                                <a href="{{ route('flatshares.show', $activeFlatshare) }}" class="inline-flex rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">
                                    {{ __('Open') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500">{{ __('No active flatshare found.') }}</p>
                    @endif
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Other flatshares') }}</h3>
                    <div class="mt-4 space-y-3">
                        @forelse($archivedFlatshares as $flatshare)
                            <a href="{{ route('flatshares.show', $flatshare) }}" class="block rounded-md border border-gray-200 px-4 py-4 hover:bg-gray-50">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $flatshare->name }}</p>
                                        <p class="text-sm text-gray-500">{{ __('Owner') }}: {{ $flatshare->owner->name }}</p>
                                    </div>
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-gray-600">
                                        {{ $flatshare->status }}
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No archived flatshares.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
