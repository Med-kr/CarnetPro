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
<body class="cp-shell min-h-screen text-stone-100">
    @php($formOnLeft = request()->routeIs('register'))
    <main class="cp-shell-frame flex min-h-[100dvh] flex-col justify-center py-4 sm:py-6 lg:py-8">
        <div class="grid w-full gap-4 rounded-[1.75rem] border border-white/25 bg-white/10 p-3 shadow-[0_24px_80px_rgba(16,43,114,0.24)] backdrop-blur-xl md:gap-6 md:p-5 xl:grid-cols-[1.08fr,0.92fr] xl:rounded-[2.25rem] xl:p-8">
            <div class="cp-grid-glow rounded-[1.5rem] bg-gradient-to-br from-[#173a8f]/95 via-[#2f67d8]/88 to-[#7ad2ee]/70 p-5 sm:p-6 xl:rounded-[2rem] xl:p-8 {{ $formOnLeft ? 'xl:order-2' : 'xl:order-1' }}">
                <div class="flex items-center gap-4">
                    <x-carnetpro-logo theme="dark" show-tagline="true" />
                </div>
                <div class="cp-guest-theme-row mt-6">
                    <div class="toggleWrapper cp-guest-theme-toggle">
                        <input class="input" id="dn-guest" type="checkbox" data-theme-input aria-label="Basculer entre le mode clair et sombre" />
                        <label class="toggle" for="dn-guest" aria-hidden="true">
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
                </div>
                <div class="mt-8 max-w-xl space-y-4 sm:mt-10 xl:mt-12">
                    <p class="cp-kicker !text-white/70">Colocation control</p>
                    <h1 class="text-3xl font-semibold leading-tight sm:text-4xl xl:text-5xl">Partagez les depenses avec une interface nette et memorisable.</h1>
                    <p class="text-sm text-white/78 sm:text-base">Suivez qui a paye, qui doit quoi, et gardez une vision globale de la colocation avec une interface inspiree du logo CarnetPro.</p>
                </div>
                <div class="mt-8 grid gap-3 sm:grid-cols-3 xl:mt-10 xl:gap-4">
                    <div class="rounded-[1.5rem] bg-white/14 p-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-white/55">Balances</p>
                        <p class="mt-3 text-3xl font-semibold">Live</p>
                    </div>
                    <div class="rounded-[1.5rem] bg-white/14 p-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-white/55">Invites</p>
                        <p class="mt-3 text-3xl font-semibold">Token</p>
                    </div>
                    <div class="rounded-[1.5rem] bg-white/14 p-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-white/55">Admin</p>
                        <p class="mt-3 text-3xl font-semibold">Global</p>
                    </div>
                </div>
            </div>
            <div class="cp-glass rounded-[1.5rem] p-5 text-stone-900 sm:p-6 xl:rounded-[2rem] xl:p-8 {{ $formOnLeft ? 'xl:order-1' : 'xl:order-2' }}">
                @if($errors->any())
                    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>

        <footer class="mt-4 w-full sm:mt-6">
            <div class="cp-footer rounded-[1.75rem] px-5 py-4 sm:px-6">
                <div class="text-center">
                    <p class="text-sm font-semibold text-slate-900">CarnetPro</p>
                    <p class="mt-1 text-xs text-slate-500">Une application claire pour rejoindre une colocation et comprendre qui doit quoi.</p>
                    <p class="mt-3 text-xs text-slate-500">Copyright 2026 CarnetPro. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </main>
    @if(! file_exists(public_path('build/manifest.json')))
        <script>
            (() => {
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
                        document.documentElement.setAttribute('data-theme', next);
                        syncButtons(next);
                    });
                });
            })();
        </script>
    @endif
</body>
</html>
