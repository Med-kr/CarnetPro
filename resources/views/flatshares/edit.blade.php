<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit flatshare') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('flatshares.update', $flatshare) }}" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="name" :value="__('Flatshare name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $flatshare->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('flatshares.show', $flatshare) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">
                            {{ __('Back') }}
                        </a>
                        <x-primary-button>{{ __('Save changes') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
