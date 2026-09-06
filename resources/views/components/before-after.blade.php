@props(['results'])

{{--
    Comparateur avant / après piloté par les vraies images uploadées depuis
    /admin/resultats. Chaque transformation est un curseur indépendant, rendu
    côté serveur (référencement + fonctionne sans JavaScript), enrichi par
    Alpine pour le glissement. Les URL d'images sont résolues côté modèle sur
    le disque "public" — jamais d'URL localhost en base.
--}}
<div class="mx-auto mt-14 max-w-4xl space-y-16">
    @foreach ($results as $result)
        <figure x-data="{ pos: 50 }">
            <div class="relative select-none overflow-hidden">
                <div class="relative aspect-[16/10] w-full bg-sand">
                    <img
                        src="{{ $result->after_image_url }}"
                        alt="Après — {{ $result->title }}"
                        class="absolute inset-0 h-full w-full object-cover"
                        loading="lazy" draggable="false"
                    >
                    <img
                        src="{{ $result->before_image_url }}"
                        alt="Avant — {{ $result->title }}"
                        class="absolute inset-0 h-full w-full object-cover"
                        :style="`clip-path: inset(0 ${100 - pos}% 0 0)`"
                        style="clip-path: inset(0 50% 0 0)"
                        loading="lazy" draggable="false"
                    >

                    <span class="absolute left-4 top-4 bg-cocoa px-3 py-1 text-[0.625rem] font-semibold uppercase tracking-[0.2em] text-white">Avant</span>
                    <span class="absolute right-4 top-4 bg-cream px-3 py-1 text-[0.625rem] font-semibold uppercase tracking-[0.2em] text-cocoa">Après</span>

                    <div class="pointer-events-none absolute inset-y-0 w-px bg-white" :style="`left: ${pos}%`" style="left:50%">
                        <span class="absolute left-1/2 top-1/2 flex h-10 w-10 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white text-cocoa shadow-lg">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m9 6-6 6 6 6M15 6l6 6-6 6"/></svg>
                        </span>
                    </div>
                </div>

                <input
                    type="range" min="0" max="100" x-model="pos" value="50"
                    aria-label="Comparateur avant / après — {{ $result->title }}"
                    class="mt-4 w-full cursor-pointer py-2 accent-taupe"
                >
            </div>

            <figcaption class="mt-8 border-t border-ink/10 pt-6">
                <p class="font-serif text-xl text-cocoa">{{ $result->title }}</p>
                @if ($result->hair_type || $result->lissage_type || $result->description)
                    <dl class="mt-4 grid gap-6 sm:grid-cols-3">
                        @if ($result->hair_type)
                            <div><dt class="eyebrow">Type de cheveux</dt><dd class="mt-1.5 text-sm text-ink/75">{{ $result->hair_type }}</dd></div>
                        @endif
                        @if ($result->lissage_type)
                            <div><dt class="eyebrow">Type de lissage</dt><dd class="mt-1.5 text-sm text-ink/75">{{ $result->lissage_type }}</dd></div>
                        @endif
                        @if ($result->description)
                            <div><dt class="eyebrow">Résultat</dt><dd class="mt-1.5 text-sm text-ink/75">{{ $result->description }}</dd></div>
                        @endif
                    </dl>
                @endif
            </figcaption>
        </figure>
    @endforeach
</div>
