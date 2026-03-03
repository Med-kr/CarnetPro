@props([
    'theme' => 'light',
    'showTagline' => false,
])

@php
    $isDark = $theme === 'dark';
    $wordPrimary = $isDark ? '#ffffff' : '#0A2540';
    $wordAccent = '#3DDAD7';
    $tagColor = $isDark ? 'rgba(255,255,255,0.72)' : '#64748B';
    $ringStroke = $isDark ? 'rgba(255,255,255,0.8)' : '#123E66';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
    <svg viewBox="0 0 64 64" aria-hidden="true" class="h-12 w-12 shrink-0 sm:h-13 sm:w-13">
        <defs>
            <linearGradient id="cpLogoCircle" x1="12" x2="52" y1="10" y2="54" gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="#3DDAD7" />
                <stop offset="0.55" stop-color="#00ADB5" />
                <stop offset="1" stop-color="#0A2540" />
            </linearGradient>
            <linearGradient id="cpLogoPaper" x1="20" x2="45" y1="18" y2="45" gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="#ffffff" />
                <stop offset="1" stop-color="#dceaff" />
            </linearGradient>
        </defs>

        <circle cx="32" cy="32" r="27.5" fill="none" stroke="{{ $ringStroke }}" stroke-width="3" />
        <circle cx="32" cy="32" r="24" fill="url(#cpLogoCircle)" />

        <path d="M20.5 18h18.4c2.2 0 3.8 1.6 3.8 3.7v20.8c0 2-1.6 3.6-3.6 3.6H21.4c-2 0-3.6-1.6-3.6-3.6V21.7c0-2.1 1.6-3.7 3.7-3.7Z" fill="url(#cpLogoPaper)" opacity="0.98" />
        <path d="M25.3 18.8v26.4M42 22.4c-4.3-1.2-8.5-.5-12.5 2v15.6c4-2.4 8.2-3.1 12.5-2" fill="none" stroke="#123E66" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.7" />
        <path d="M23.2 26.4 19.4 29l3.8 2.6M40.8 31.6l3.8-2.6-3.8-2.6" fill="none" stroke="#3DDAD7" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" />
        <path d="M33.4 27.2h4.8M33.4 31.3h4.5M33.4 35.4h5.1" stroke="#00ADB5" stroke-linecap="round" stroke-width="2.3" />
    </svg>

    <div class="pt-1 leading-none sm:pt-0">
        <div class="text-[1.9rem] font-semibold tracking-tight sm:text-[2rem]">
            <span style="color: {{ $wordPrimary }}">Carnet</span><span style="color: {{ $wordAccent }}">Pro</span>
        </div>
        @if($showTagline)
            <div class="mt-1 whitespace-nowrap text-[0.68rem] tracking-[0.14em] uppercase sm:text-xs sm:tracking-[0.16em]" style="color: {{ $tagColor }}">
                Shared flatshare ledger
            </div>
        @endif
    </div>
</div>
