@props(['title' => 'Administration'])
@php
    $navItems = [
        ['route' => 'admin.dashboard', 'label' => 'Tableau de bord', 'match' => 'admin.dashboard'],
        ['route' => 'admin.appointments.index', 'label' => 'Rendez-vous', 'match' => 'admin.appointments.*'],
        ['route' => 'admin.calendar', 'label' => 'Calendrier', 'match' => 'admin.calendar*'],
        ['route' => 'admin.clients.index', 'label' => 'Clientes', 'match' => 'admin.clients.*'],
        ['route' => 'admin.services.index', 'label' => 'Prestations', 'match' => 'admin.services.*'],
        ['route' => 'admin.results.index', 'label' => 'Résultats avant / après', 'match' => 'admin.results.*'],
        ['route' => 'admin.testimonials.index', 'label' => 'Témoignages', 'match' => 'admin.testimonials.*'],
        ['route' => 'admin.pricing.index', 'label' => 'Tarifs', 'match' => 'admin.pricing.*'],
        ['route' => 'admin.site-content.index', 'label' => 'Apparence du site', 'match' => 'admin.site-content.*'],
        ['route' => 'admin.availability.index', 'label' => 'Disponibilités', 'match' => 'admin.availability.*'],
        ['route' => 'admin.settings.index', 'label' => 'Paramètres', 'match' => 'admin.settings.*'],
    ];
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} — AylaLisse Administration</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-screen bg-sand text-ink" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        {{-- Sidebar bureau + drawer mobile --}}
        <aside
            class="fixed inset-y-0 left-0 z-40 flex w-72 flex-col bg-cocoa transition-transform duration-200 lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center justify-between px-6 py-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 leading-none">
                    <x-brand-mark variant="light" class="h-7 w-7 shrink-0" />
                    <span class="flex flex-col">
                        <span class="font-serif text-xl font-semibold text-white">AylaLisse</span>
                        <span class="mt-1 text-[0.625rem] font-semibold uppercase tracking-[0.2em] text-taupe">Administration</span>
                    </span>
                </a>
                <button type="button" class="text-white/60 lg:hidden" @click="sidebarOpen = false" aria-label="Fermer le menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="mt-2 flex-1 space-y-0.5 px-3">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="sidebar-link {{ request()->routeIs($item['match']) ? 'is-active' : '' }}">
                        {{ strtoupper($item['label']) }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-white/10 px-6 py-5">
                <p class="text-sm font-medium text-white">{{ auth('admin')->user()?->name }}</p>
                <p class="text-xs text-white/40">{{ auth('admin')->user()?->email }}</p>
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-taupe hover:text-white">
                        Déconnexion
                    </button>
                </form>
            </div>
        </aside>

        {{-- Voile mobile --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-cocoa/40 lg:hidden"
        ></div>

        {{-- Contenu --}}
        <div class="flex min-h-screen flex-1 flex-col lg:pl-0">
            <header class="flex items-center justify-between border-b border-nude/40 bg-cream px-4 py-3 lg:hidden">
                <button type="button" class="flex h-9 w-9 items-center justify-center border border-nude/50 text-cocoa" @click="sidebarOpen = true" aria-label="Ouvrir le menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                </button>
                <span class="flex items-center gap-2">
                    <x-brand-mark class="h-5 w-5 shrink-0" />
                    <span class="font-serif text-lg text-cocoa">AylaLisse</span>
                </span>
                <span class="w-9"></span>
            </header>

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-10 lg:py-10">
                <div class="mx-auto max-w-6xl">
                    @if (session('success'))
                        <div class="mb-6 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>
</html>
