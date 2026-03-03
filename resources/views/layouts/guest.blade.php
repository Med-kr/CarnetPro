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
        <div class="cp-guest-shell mx-auto grid w-full max-w-[88rem] gap-4 rounded-[1.75rem] p-3 md:gap-5 md:p-4 xl:grid-cols-[1fr,0.88fr] xl:rounded-[2.25rem] xl:p-5">
            <div class="cp-grid-glow cp-guest-hero relative overflow-hidden rounded-[1.5rem] p-5 sm:p-6 xl:rounded-[2rem] xl:p-6 {{ $formOnLeft ? 'xl:order-2' : 'xl:order-1' }}">
                <div class="cp-guest-orb cp-guest-orb-one"></div>
                <div class="cp-guest-orb cp-guest-orb-two"></div>

                <div class="relative z-10 flex items-center gap-4">
                    <x-carnetpro-logo theme="dark" show-tagline="true" />
                </div>

                <div class="relative z-10 cp-guest-theme-row mt-6">
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

                <div class="relative z-10 mt-7 max-w-[32rem] space-y-4 sm:mt-8 xl:mt-9">
                    <p class="cp-kicker !text-white/70">Colocation control</p>
                    <h1 class="text-3xl font-semibold leading-tight sm:text-4xl xl:text-[3.15rem]">Partagez les depenses avec une interface plus claire, plus rapide et plus confiante.</h1>
                    <p class="text-sm text-white/78 sm:text-base">Suivez chaque depense, reglez les soldes plus vite et gardez une vision propre de votre colocation avec une interface qui assume enfin son identite.</p>
                </div>

                <div class="relative z-10 mt-6 grid gap-3 sm:grid-cols-3 xl:mt-7 xl:gap-3">
                    <div class="cp-guest-metric rounded-[1.5rem] p-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-white/55">Balances</p>
                        <p class="mt-3 text-3xl font-semibold">Live</p>
                        <p class="mt-2 text-xs text-white/70">Calculs continus et lisibles</p>
                    </div>
                    <div class="cp-guest-metric rounded-[1.5rem] p-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-white/55">Invites</p>
                        <p class="mt-3 text-3xl font-semibold">Token</p>
                        <p class="mt-2 text-xs text-white/70">Acces securise par email</p>
                    </div>
                    <div class="cp-guest-metric rounded-[1.5rem] p-4">
                        <p class="text-xs uppercase tracking-[0.25em] text-white/55">Admin</p>
                        <p class="mt-3 text-3xl font-semibold">Global</p>
                        <p class="mt-2 text-xs text-white/70">Controle et moderation centralises</p>
                    </div>
                </div>
            </div>

            <div class="cp-glass cp-guest-form rounded-[1.5rem] p-5 text-stone-900 sm:p-6 xl:rounded-[2rem] xl:p-6 {{ $formOnLeft ? 'xl:order-1' : 'xl:order-2' }}">
                @if($errors->any())
                    <div class="mb-4 rounded-2xl border border-rose-200/90 bg-rose-50/95 px-4 py-3 text-sm text-rose-800 shadow-[0_10px_30px_rgba(244,63,94,0.12)]">
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

        <footer class="mt-4 w-full sm:mt-5">
            <div class="cp-footer rounded-[1.5rem] px-4 py-3.5 sm:px-5 sm:py-4">
                <div class="text-center">
                    <p class="text-sm font-semibold text-slate-50">CarnetPro</p>
                    <p class="mt-1 text-xs leading-5 text-slate-300 sm:text-sm">
                        Une application claire pour suivre les depenses, les soldes et les remboursements en colocation.
                    </p>
                </div>

                <div class="mt-3 border-t border-white/10 pt-2.5 text-center text-[11px] text-slate-400 sm:text-xs">
                    <p>Copyright 2026 CarnetPro. All rights reserved.</p>
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
