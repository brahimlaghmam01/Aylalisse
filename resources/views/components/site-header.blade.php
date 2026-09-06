@php
    $links = [
        ['label' => 'Accueil',        'href' => url('/') . '#accueil'],
        ['label' => 'Le lissage',     'href' => url('/') . '#le-lissage'],
        ['label' => 'Résultats',      'href' => url('/') . '#resultats'],
        ['label' => 'Notre méthode',  'href' => url('/') . '#notre-methode'],
        ['label' => 'Témoignages',    'href' => url('/') . '#temoignages'],
        ['label' => 'FAQ',            'href' => url('/') . '#faq'],
        ['label' => 'Contact',        'href' => url('/') . '#contact'],
    ];
@endphp

<header
    x-data="{ open: false, scrolled: false }"
    x-init="scrolled = window.scrollY > 20; window.addEventListener('scroll', () => scrolled = window.scrollY > 20)"
    class="sticky top-0 z-50 transition-colors duration-300"
    :class="scrolled || open ? 'bg-cream/95 backdrop-blur border-b border-nude/30' : 'bg-transparent'"
>
    {{-- Bandeau annonce --}}
    <div class="hidden bg-cocoa text-center text-white lg:block">
        <p class="py-2 text-[0.6875rem] font-semibold uppercase tracking-[0.22em]">
            Haute coiffure &amp; lissage d’exception — Diagnostic personnalisé offert
        </p>
    </div>

    <div class="container-editorial">
        <div class="flex items-center justify-between py-5">
            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 leading-none">
                <x-brand-mark class="h-5 w-5 shrink-0" />
                <span class="flex flex-col">
                    <span class="font-serif text-2xl font-semibold tracking-tight text-cocoa">AylaLisse</span>
                    <span class="mt-1 text-[0.625rem] font-semibold uppercase tracking-[0.22em] text-taupe">Spécialiste du lissage</span>
                </span>
            </a>

            {{-- Navigation bureau --}}
            <nav class="hidden items-center gap-8 xl:flex">
                @foreach ($links as $link)
                    <a href="{{ $link['href'] }}" class="text-[0.8125rem] font-medium text-ink/80 transition-colors hover:text-cocoa">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('booking') }}" class="btn btn-primary hidden sm:inline-flex">Prendre rendez-vous</a>

                {{-- Bouton menu mobile --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex h-11 w-11 items-center justify-center border border-nude/50 text-cocoa xl:hidden"
                    :aria-expanded="open"
                    aria-label="Ouvrir le menu"
                >
                    <svg x-show="!open" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                    <svg x-show="open" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Menu mobile plein écran --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="border-t border-nude/30 bg-cream xl:hidden"
    >
        <nav class="container-editorial flex flex-col divide-y divide-nude/20 py-2">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" @click="open = false" class="py-4 font-serif text-2xl text-cocoa">
                    {{ $link['label'] }}
                </a>
            @endforeach
            <a href="{{ route('booking') }}" @click="open = false" class="btn btn-primary mt-5 mb-3">Prendre rendez-vous</a>
        </nav>
    </div>
</header>
