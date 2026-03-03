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
            <div class="flex items-center gap-3">
                @foreach(['members' => 'Members', 'expenses' => 'Expenses', 'categories' => 'Categories'] as $key => $label)
                    <a
                        href="{{ route('flatshares.show', ['flatshare' => $flatshare, 'tab' => $key]) }}"
                        class="rounded-md px-4 py-2 text-sm font-semibold {{ $tab === $key ? 'bg-gray-900 text-white' : 'border border-gray-300 text-gray-700' }}"
                    >
                        {{ __($label) }}
                    </a>
                @endforeach
            </div>

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

            @if($tab === 'members')
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
            @endif

            @if($tab === 'expenses')
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Add expense') }}</h3>
                        @can('create', [App\Models\Expense::class, $flatshare])
                            <form method="POST" action="{{ route('flatshares.expenses.store', $flatshare) }}" class="mt-4 grid gap-4 md:grid-cols-2">
                                @csrf
                                <div class="md:col-span-2">
                                    <x-input-label for="expense_title" :value="__('Title')" />
                                    <x-text-input id="expense_title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
                                </div>
                                <div>
                                    <x-input-label for="expense_amount" :value="__('Amount')" />
                                    <x-text-input id="expense_amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                                </div>
                                <div>
                                    <x-input-label for="expense_spent_at" :value="__('Date')" />
                                    <x-text-input id="expense_spent_at" name="spent_at" type="date" class="mt-1 block w-full" :value="old('spent_at', now()->toDateString())" required />
                                </div>
                                <div>
                                    <x-input-label for="expense_payer_id" :value="__('Paid by')" />
                                    <select id="expense_payer_id" name="payer_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($flatshare->activeMemberships as $membership)
                                            <option value="{{ $membership->user_id }}">{{ $membership->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="expense_category_id" :value="__('Category')" />
                                    <select id="expense_category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">{{ __('No category') }}</option>
                                        @foreach($flatshare->categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <x-primary-button>{{ __('Add expense') }}</x-primary-button>
                                </div>
                            </form>
                        @endcan

                        <div class="mt-8 space-y-3">
                            @forelse($flatshare->expenses as $expense)
                                <div class="rounded-md border border-gray-200 px-4 py-3">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $expense->title }}</p>
                                            <p class="text-sm text-gray-500">
                                                {{ $expense->payer->name }} · {{ number_format($expense->amount, 2) }} · {{ $expense->spent_at->format('Y-m-d') }}
                                                @if($expense->category)
                                                    · {{ $expense->category->name }}
                                                @endif
                                            </p>
                                        </div>
                                        @can('delete', $expense)
                                            <form method="POST" action="{{ route('flatshares.expenses.destroy', [$flatshare, $expense]) }}">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button>{{ __('Delete') }}</x-danger-button>
                                            </form>
                                        @endcan
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('No expenses recorded yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            @if($tab === 'categories')
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Categories') }}</h3>
                            <span class="text-sm text-gray-500">{{ $flatshare->categories->count() }}</span>
                        </div>

                        @can('create', [App\Models\Category::class, $flatshare])
                            <form method="POST" action="{{ route('flatshares.categories.store', $flatshare) }}" class="mt-4 grid gap-3 md:grid-cols-[1.5fr,1fr,auto]">
                                @csrf
                                <div>
                                    <x-input-label for="category_name" :value="__('Category name')" />
                                    <x-text-input id="category_name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                                </div>
                                <div>
                                    <x-input-label for="category_icon" :value="__('Icon')" />
                                    <select id="category_icon" name="icon" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach(App\Models\Category::iconOptions() as $value => $option)
                                            <option value="{{ $value }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <x-primary-button>{{ __('Add') }}</x-primary-button>
                                </div>
                            </form>
                        @endcan

                        <div class="mt-6 space-y-3">
                            @forelse($flatshare->categories as $category)
                                <div class="rounded-md border border-gray-200 px-4 py-3">
                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $category->name }}</p>
                                            <p class="text-sm text-gray-500">{{ $category->iconLabel() }}</p>
                                        </div>

                                        @can('update', $category)
                                            <div class="flex flex-col gap-3 lg:flex-row">
                                                <form method="POST" action="{{ route('flatshares.categories.update', [$flatshare, $category]) }}" class="flex flex-col gap-3 sm:flex-row">
                                                    @csrf
                                                    @method('PUT')
                                                    <x-text-input name="name" type="text" :value="$category->name" required />
                                                    <select name="icon" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                        @foreach(App\Models\Category::iconOptions() as $value => $option)
                                                            <option value="{{ $value }}" @selected($category->icon === $value)>{{ $option['label'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                                                </form>

                                                <form method="POST" action="{{ route('flatshares.categories.destroy', [$flatshare, $category]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-danger-button>{{ __('Delete') }}</x-danger-button>
                                                </form>
                                            </div>
                                        @endcan
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">{{ __('No categories configured yet.') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Invite a member') }}</h3>

                    @can('invite', $flatshare)
                        <form method="POST" action="{{ route('flatshares.invitations.store', $flatshare) }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                            @csrf
                            <x-text-input name="email" type="email" class="block w-full" :value="old('email')" placeholder="member@example.com" required />
                            <x-primary-button>{{ __('Send invitation') }}</x-primary-button>
                        </form>
                    @endcan

                    @if(session('invitation_url'))
                        <div class="mt-4 rounded-md border border-sky-200 bg-sky-50 px-4 py-4 text-sm text-sky-900">
                            <p class="font-semibold">{{ __('Invitation link') }}</p>
                            <p class="mt-2 break-all">{{ session('invitation_url') }}</p>
                        </div>
                    @endif

                    <div class="mt-6 space-y-3">
                        @forelse($flatshare->invitations()->latest()->get() as $invitation)
                            <div class="rounded-md border border-gray-200 px-4 py-3">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $invitation->email }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ ucfirst($invitation->status) }} · {{ $invitation->expires_at?->format('Y-m-d H:i') }}
                                        </p>
                                    </div>
                                    @can('invite', $flatshare)
                                        <form method="POST" action="{{ route('flatshares.invitations.destroy', [$flatshare, $invitation]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-danger-button>{{ __('Delete') }}</x-danger-button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('No invitations sent yet.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
