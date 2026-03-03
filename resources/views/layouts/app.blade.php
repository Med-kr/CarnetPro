<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CarnetPro') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('carnetpro-favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="{{ asset('carnetpro-fallback.css') }}">
    @endif
    <script>
        (() => {
            const saved = localStorage.getItem('carnetpro-theme');
            const theme = saved || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body class="cp-shell min-h-screen text-stone-900">
    @php
        $activeFlatshare = auth()->check() ? auth()->user()->activeMembership()->with('flatshare')->first()?->flatshare : null;
        $mobileExpensesRoute = $activeFlatshare
            ? route('flatshares.show', ['flatshare' => $activeFlatshare, 'tab' => 'expenses'])
            : route('flatshares.index');
        $mobileExpenseRoute = $activeFlatshare
            ? route('flatshares.show', ['flatshare' => $activeFlatshare, 'tab' => 'expenses'])
            : route('flatshares.create');
        $mobileSettlementsRoute = $activeFlatshare
            ? route('flatshares.settlements.show', $activeFlatshare)
            : route('flatshares.index');
        $mobileUtilityRoute = auth()->check() && auth()->user()?->is_global_admin
            ? route('admin.index')
            : route('profile.edit');
        $mobileUtilityLabel = auth()->check() && auth()->user()?->is_global_admin ? 'Admin' : 'Profil';
    @endphp
    <nav class="cp-shell-frame pt-4 sm:pt-5">
        <div class="cp-gradient-panel rounded-[2rem] px-4 py-4 text-white sm:px-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="block">
                        <x-carnetpro-logo theme="dark" show-tagline="true" />
                    </a>
                    @auth
                        <div class="flex items-center gap-2 md:hidden">
                            <div class="toggleWrapper">
                                <input class="input" id="dn-app-mobile" type="checkbox" data-theme-input aria-label="Basculer entre le mode clair et sombre" />
                                <label class="toggle" for="dn-app-mobile" aria-hidden="true">
                                    <span class="toggle__handler">
                                        <span class="crater crater--1"></span>
                                        <span class="crater crater--2"></span>
                                        <span class="crater crater--3"></span>
                                    </span>
                                    <span class="star star--1"></span>
                                    <span class="star star--2"></span>
                                    <span class="star star--3"></span>
                                    <span class="star star--4"></span>
                                    <span class="star star--5"></span>
                                    <span class="star star--6"></span>
                                </label>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 bg-white/10 text-sm font-semibold text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        </div>
                    @endauth
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="cp-nav-link hidden rounded-full px-4 py-2 md:inline-flex">Dashboard</a>
                        <a href="{{ route('flatshares.index') }}" class="cp-nav-link hidden rounded-full px-4 py-2 md:inline-flex">Flatshares</a>
                        <a href="{{ route('profile.edit') }}" class="cp-nav-link hidden rounded-full px-4 py-2 md:inline-flex">Profile</a>
                        @if(auth()->user()?->is_global_admin)
                            <a href="{{ route('admin.index') }}" class="hidden rounded-full bg-white/18 px-4 py-2 text-white ring-1 ring-white/20 md:inline-flex">Admin</a>
                        @endif
                        <div class="hidden rounded-full border border-white/15 bg-white/10 px-4 py-2 text-white/80 md:inline-flex">
                            {{ auth()->user()->name }}
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                            @csrf
                            <button class="rounded-full bg-white px-4 py-2 font-medium text-[#1f4fb9]">Logout</button>
                        </form>
                        <div class="toggleWrapper order-last hidden md:order-none md:inline-flex">
                            <input class="input" id="dn-app" type="checkbox" data-theme-input aria-label="Basculer entre le mode clair et sombre" />
                            <label class="toggle" for="dn-app" aria-hidden="true">
                                <span class="toggle__handler">
                                    <span class="crater crater--1"></span>
                                    <span class="crater crater--2"></span>
                                    <span class="crater crater--3"></span>
                                </span>
                                <span class="star star--1"></span>
                                <span class="star star--2"></span>
                                <span class="star star--3"></span>
                                <span class="star star--4"></span>
                                <span class="star star--5"></span>
                                <span class="star star--6"></span>
                            </label>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="cp-nav-link rounded-full px-4 py-2">Login</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-white px-4 py-2 font-medium text-[#1f4fb9]">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="cp-main-shell cp-shell-frame py-6 sm:py-8">
        @if(session('success'))
            <div class="cp-glass mb-6 rounded-[1.5rem] border border-emerald-200/60 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="cp-glass mb-6 rounded-[1.5rem] border border-amber-200/70 px-4 py-3 text-sm text-amber-800">
                {{ session('warning') }}
            </div>
        @endif

        @if($errors->any())
            <div class="cp-glass mb-6 rounded-[1.5rem] border border-rose-200/60 px-4 py-3 text-sm text-rose-800">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <footer class="cp-shell-frame pb-24 md:pb-8">
        <div class="cp-footer rounded-[1.75rem] px-5 py-4 sm:px-6">
            <div class="text-center">
                <p class="text-sm font-semibold text-slate-900">CarnetPro</p>
                <p class="mt-1 text-xs text-slate-500">Une application claire pour suivre les depenses, les soldes et les remboursements en colocation.</p>
                <p class="mt-3 text-xs text-slate-500">Copyright 2026 CarnetPro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @auth
        <nav class="cp-mobile-nav fixed inset-x-4 bottom-4 z-50 rounded-[1.75rem] px-3 pb-3 pt-4 md:hidden">
            <div class="grid grid-cols-5 gap-1 text-center text-[11px] font-medium">
                <a href="{{ route('dashboard') }}" class="cp-mobile-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <span class="cp-mobile-icon">
                        <svg viewBox="0 0 24 24" class="h-4.5 w-4.5 fill-current"><path d="M12 3.2 3 10.4v9.4h5.8v-5.7h6.4v5.7H21v-9.4Z"/></svg>
                    </span>
                    <span>Accueil</span>
                </a>
                <a href="{{ $mobileExpensesRoute }}" class="cp-mobile-link {{ request()->routeIs('flatshares.show') || request()->routeIs('flatshares.expenses.*') ? 'is-active' : '' }}">
                    <span class="cp-mobile-icon">
                        <svg viewBox="0 0 24 24" class="h-4.5 w-4.5 fill-current"><path d="M5 4.5h14a1 1 0 0 1 1 1v13.2a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5.5a1 1 0 0 1 1-1Zm2 2v2.4h3V6.5Zm0 4.3v2.3h3v-2.3Zm0 4.2v2.3h3V15Zm5-8.5v2.4h5V6.5Zm0 4.3v2.3h5v-2.3Zm0 4.2v2.3h5V15Z"/></svg>
                    </span>
                    <span>Depenses</span>
                </a>
                <a href="{{ $mobileExpenseRoute }}" class="cp-mobile-link cp-mobile-primary">
                    <span class="cp-mobile-icon">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M11 5h2v14h-2z"/><path d="M5 11h14v2H5z"/></svg>
                    </span>
                    <span>Depense</span>
                </a>
                <a href="{{ $mobileSettlementsRoute }}" class="cp-mobile-link {{ request()->routeIs('flatshares.settlements.*') ? 'is-active' : '' }}">
                    <span class="cp-mobile-icon">
                        <svg viewBox="0 0 24 24" class="h-4.5 w-4.5 fill-current"><path d="M4 7.5h8v4H4Zm0 5h6v4H4Zm10-6.8 6 3.4-6 3.4Zm0 6.8 6 3.4-6 3.4Z"/></svg>
                    </span>
                    <span>Soldes</span>
                </a>
                <a href="{{ $mobileUtilityRoute }}" class="cp-mobile-link {{ request()->routeIs('admin.*') || request()->routeIs('profile.*') ? 'is-active' : '' }}">
                    <span class="cp-mobile-icon">
                        @if(auth()->user()?->is_global_admin)
                            <svg viewBox="0 0 24 24" class="h-4.5 w-4.5 fill-current"><path d="M12 2.8 4 6v5.2c0 4.6 3.4 8.8 8 10 4.6-1.2 8-5.4 8-10V6Zm0 4.1a2.2 2.2 0 1 1-2.2 2.2A2.2 2.2 0 0 1 12 6.9Zm0 10.2a5.84 5.84 0 0 1-4.2-1.8 4.74 4.74 0 0 1 8.4 0 5.84 5.84 0 0 1-4.2 1.8Z"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" class="h-4.5 w-4.5 fill-current"><path d="M12 12a4.5 4.5 0 1 0-4.5-4.5A4.5 4.5 0 0 0 12 12Zm0 2.2c-4.4 0-8 2.3-8 5.1v.7h16v-.7c0-2.8-3.6-5.1-8-5.1Z"/></svg>
                        @endif
                    </span>
                    <span>{{ $mobileUtilityLabel }}</span>
                </a>
            </div>
        </nav>
    @endauth

    @if(! file_exists(public_path('build/manifest.json')))
        <script>
            (() => {
                const applyTheme = (theme) => {
                    document.documentElement.setAttribute('data-theme', theme);
                };
                const syncButtons = (theme) => {
                    document.querySelectorAll('[data-theme-input]').forEach((input) => {
                        input.checked = theme === 'dark';
                    });
                };
                const current = document.documentElement.getAttribute('data-theme') || 'light';
                syncButtons(current);
                document.querySelectorAll('[data-theme-input]').forEach((input) => {
                    input.addEventListener('change', () => {
                        const next = input.checked ? 'dark' : 'light';
                        localStorage.setItem('carnetpro-theme', next);
                        applyTheme(next);
                        syncButtons(next);
                    });
                });
            })();
        </script>
    @endif
</body>
</html>
