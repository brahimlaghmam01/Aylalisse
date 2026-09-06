@php $a = config('aylalisse.address'); @endphp

<x-layouts.public :structured-data="true">
    @section('title', 'AylaLisse — Spécialiste du lissage des cheveux')

    {{-- ================= HERO ================= --}}
    <section id="accueil" class="relative overflow-hidden">
        <div class="container-editorial grid items-center gap-12 pb-20 pt-14 lg:grid-cols-2 lg:gap-16 lg:pb-28 lg:pt-20">
            <div>
                <p class="eyebrow">Haute coiffure • Lissage d’exception</p>
                <h1 class="display-1 mt-6 text-balance">Une confiance naturellement lisse.</h1>
                <p class="mt-6 max-w-lg text-lg text-ink/70">
                    Chez AylaLisse, chaque transformation commence par une approche personnalisée
                    pour révéler une chevelure lisse, soyeuse et élégante.
                </p>

                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('booking') }}" class="btn btn-primary">Prendre rendez-vous</a>
                    <a href="#resultats" class="btn btn-outline">Découvrir nos résultats</a>
                </div>

                <div class="mt-12 grid max-w-md grid-cols-3 gap-6 border-t border-ink/10 pt-8">
                    <div>
                        <p class="font-serif text-lg text-cocoa">Diagnostic</p>
                        <p class="mt-1 text-xs text-ink/55">personnalisé</p>
                    </div>
                    <div>
                        <p class="font-serif text-lg text-cocoa">Expérience</p>
                        <p class="mt-1 text-xs text-ink/55">premium</p>
                    </div>
                    <div>
                        <p class="font-serif text-lg text-cocoa">Expertise</p>
                        <p class="mt-1 text-xs text-ink/55">spécialisée</p>
                    </div>
                </div>
            </div>

            {{-- Visuel hero — image configurée depuis /admin/apparence, repli
                 sur un dégradé si aucune image n'a encore été choisie. --}}
            <div class="relative">
                <div class="aspect-[4/5] w-full overflow-hidden bg-gradient-to-b from-beige to-nude/70">
                    @if ($heroImage)
                        <img src="{{ $heroImage }}" alt="AylaLisse — chevelure lissée" class="h-full w-full object-cover" fetchpriority="high">
                    @else
                        <div class="flex h-full items-end p-6">
                            <span class="text-[0.625rem] font-semibold uppercase tracking-[0.22em] text-cocoa/60">Visuel — chevelure lissée</span>
                        </div>
                    @endif
                </div>
                <div class="absolute -bottom-6 -left-6 hidden bg-cream p-5 shadow-[0_20px_60px_-20px_rgba(37,21,15,0.35)] sm:block">
                    <p class="eyebrow">Résultat</p>
                    <p class="mt-1 font-serif text-xl text-cocoa">Lisse, brillant, sain</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= EXPERTISE ================= --}}
    <section id="le-lissage" class="bg-sand section-y">
        <div class="container-editorial">
            <x-section-heading
                eyebrow="Précision, expertise & résultat"
                title="Plus qu’un lissage. Une véritable transformation."
            >
                Chaque cliente reçoit une approche adaptée à ses cheveux : nature capillaire,
                traitements passés et attentes sont étudiés avant chaque rendez-vous.
            </x-section-heading>

            <div class="mt-16 grid gap-px overflow-hidden border border-nude/30 bg-nude/30 md:grid-cols-3">
                @foreach ([
                    ['Analyse personnalisée', 'Comprendre les cheveux avant même le rendez-vous, pour anticiper la formule juste.'],
                    ['Diagnostic capillaire', 'Lire la texture, la porosité et les traitements chimiques déjà réalisés.'],
                    ['Lissage maîtrisé', 'Une transformation professionnelle, calibrée au type de cheveux de chaque cliente.'],
                ] as $i => $block)
                    <div class="bg-cream p-8 lg:p-10">
                        <span class="font-serif text-3xl text-taupe">0{{ $i + 1 }}</span>
                        <h3 class="display-3 mt-4 text-2xl">{{ $block[0] }}</h3>
                        <p class="mt-3 text-sm text-ink/65">{{ $block[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= EXPÉRIENCES DE LISSAGE ================= --}}
    @php
        // Grille : 2 colonnes si peu de prestations, sinon jusqu'à 4.
        $expCols = ($services->count() ?: count($experiencesFallback)) <= 2
            ? 'md:grid-cols-2'
            : 'md:grid-cols-2 xl:grid-cols-4';
    @endphp
    <section class="section-y">
        <div class="container-editorial">
            <x-section-heading eyebrow="Nos prestations de lissage" title="Un protocole pour chaque chevelure." />

            <div class="mt-16 grid gap-6 {{ $expCols }}">
                @forelse ($services as $service)
                    <article class="card-soft flex flex-col overflow-hidden">
                        @if ($service->image_url)
                            <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="aspect-[4/3] w-full object-cover" loading="lazy">
                        @endif
                        <div class="flex flex-1 flex-col p-8">
                            <h3 class="display-3 text-2xl">{{ $service->name }}</h3>
                            <p class="mt-3 flex-1 text-sm text-ink/65">{{ $service->short_description }}</p>

                            <dl class="mt-6 space-y-1 text-sm text-ink/70">
                                <div class="flex justify-between border-t border-ink/10 pt-3">
                                    <dt>Durée</dt><dd>{{ $service->durationLabel() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt>Tarif</dt>
                                    <dd class="font-serif text-cocoa">{{ $service->publicPriceLabel() }}</dd>
                                </div>
                            </dl>

                            <a href="{{ route('booking') }}" class="btn btn-outline mt-6">Réserver</a>
                        </div>
                    </article>
                @empty
                    {{-- Repli : aucune prestation active en base. --}}
                    @foreach ($experiencesFallback as $exp)
                        <article @class([
                            'flex flex-col p-8',
                            'bg-cocoa text-cream' => $exp['featured'],
                            'card-soft' => ! $exp['featured'],
                        ])>
                            @if ($exp['featured'])
                                <span class="eyebrow text-taupe">Le plus demandé</span>
                            @endif
                            <h3 @class(['display-3 text-2xl mt-2', 'text-white' => $exp['featured']])>{{ $exp['nom'] }}</h3>
                            <p @class(['mt-3 text-sm flex-1', 'text-cream/70' => $exp['featured'], 'text-ink/65' => ! $exp['featured']])>
                                {{ $exp['description'] }}
                            </p>
                            <dl @class(['mt-6 space-y-1 text-sm', 'text-cream/80' => $exp['featured'], 'text-ink/70' => ! $exp['featured']])>
                                <div class="flex justify-between border-t {{ $exp['featured'] ? 'border-white/15' : 'border-ink/10' }} pt-3">
                                    <dt>Durée</dt><dd>{{ $exp['duree'] }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt>À partir de</dt>
                                    <dd>{{ $exp['prix'] ? $exp['prix'].' €' : 'sur devis' }}</dd>
                                </div>
                            </dl>
                            <a href="{{ route('booking') }}" @class(['btn mt-6', 'btn-light' => $exp['featured'], 'btn-outline' => ! $exp['featured']])>Réserver</a>
                        </article>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- ================= MÉTHODE ================= --}}
    <section id="notre-methode" class="bg-sand section-y">
        <div class="container-editorial">
            <x-section-heading eyebrow="La méthode AylaLisse" title="Quatre étapes vers une chevelure soyeuse." />

            <div class="mt-16 grid gap-10 md:grid-cols-2 xl:grid-cols-4">
                @foreach ($methode as $etape)
                    <div>
                        <p class="font-serif text-5xl text-nude">{{ $etape['num'] }}</p>
                        <h3 class="eyebrow mt-4 text-cocoa">{{ $etape['titre'] }}</h3>
                        <p class="mt-3 text-sm text-ink/65">{{ $etape['texte'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= AVANT / APRÈS ================= --}}
    <section id="resultats" class="section-y">
        <div class="container-editorial">
            <x-section-heading align="center" eyebrow="Preuve par l’image" title="Des résultats qui parlent d’eux-mêmes." />

            @if ($results->isNotEmpty())
                <x-before-after :results="$results" />
            @else
                {{-- Aucune transformation publiée pour l'instant : visuel de
                     démonstration, remplacé automatiquement dès qu'un résultat
                     est ajouté depuis /admin/resultats. --}}
                <div x-data="{ pos: 50 }" class="relative mx-auto mt-14 max-w-4xl select-none overflow-hidden">
                    <div class="relative aspect-[16/10] w-full bg-gradient-to-r from-nude/60 to-beige">
                        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-beige to-cream">
                            <span class="text-[0.625rem] font-semibold uppercase tracking-[0.22em] text-cocoa/50">Après — lissage soyeux</span>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-taupe/70 to-nude" :style="`clip-path: inset(0 ${100 - pos}% 0 0)`">
                            <span class="text-[0.625rem] font-semibold uppercase tracking-[0.22em] text-white/80">Avant — cheveux ondulés</span>
                        </div>
                        <span class="absolute left-4 top-4 bg-cocoa px-3 py-1 text-[0.625rem] font-semibold uppercase tracking-[0.2em] text-white">Avant</span>
                        <span class="absolute right-4 top-4 bg-cream px-3 py-1 text-[0.625rem] font-semibold uppercase tracking-[0.2em] text-cocoa">Après</span>
                        <div class="pointer-events-none absolute inset-y-0 w-px bg-white" :style="`left: ${pos}%`">
                            <span class="absolute top-1/2 left-1/2 flex h-10 w-10 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white text-cocoa shadow-lg">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m9 6-6 6 6 6M15 6l6 6-6 6"/></svg>
                            </span>
                        </div>
                    </div>
                    <input type="range" min="0" max="100" x-model="pos" aria-label="Comparateur avant après" class="mt-5 w-full accent-taupe">
                </div>
            @endif
        </div>
    </section>

    {{-- ================= TARIFS ================= --}}
    @if ($priceSections->isNotEmpty())
        <section id="tarifs" class="bg-sand section-y">
            <div class="container-editorial">
                <x-section-heading align="center" eyebrow="Tarifs" :title="$pricing['title']">
                    @if ($pricing['intro'])
                        {{ $pricing['intro'] }}
                    @endif
                </x-section-heading>

                <x-price-grid :sections="$priceSections" />

                <p class="mx-auto mt-12 max-w-2xl text-center text-xs text-ink/45">
                    Tarifs indicatifs — le prix définitif est confirmé lors du diagnostic capillaire offert.
                </p>
            </div>
        </section>
    @endif

    {{-- ================= POURQUOI AYLALISSE ================= --}}
    <section class="bg-sand section-y">
        <div class="container-editorial">
            <x-section-heading eyebrow="Les atouts de la maison" title="Pourquoi choisir AylaLisse." />
            <div class="mt-16 grid gap-px overflow-hidden border border-nude/30 bg-nude/30 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($atouts as $atout)
                    <div class="bg-cream p-8">
                        <h3 class="eyebrow text-cocoa">{{ $atout['titre'] }}</h3>
                        <p class="mt-3 text-sm text-ink/65">{{ $atout['texte'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= TÉMOIGNAGES ================= --}}
    <section id="temoignages" class="section-y">
        <div class="container-editorial">
            <x-section-heading eyebrow="Avis vérifiés" title="Elles ont choisi AylaLisse." />
            <div class="mt-16 grid gap-6 md:grid-cols-3">
                @forelse ($temoignages as $t)
                    <figure class="card-soft flex flex-col p-8">
                        <div class="flex gap-1 text-taupe">
                            @for ($i = 0; $i < $t->rating; $i++)
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15.27 16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"/></svg>
                            @endfor
                        </div>
                        <blockquote class="mt-5 flex-1 font-serif text-xl leading-snug text-cocoa">« {{ $t->content }} »</blockquote>
                        <figcaption class="mt-6 flex items-center gap-3 border-t border-ink/10 pt-4 text-sm">
                            @if ($t->image_url)
                                <img src="{{ $t->image_url }}" alt="{{ $t->client_name }}" class="h-10 w-10 rounded-full object-cover" loading="lazy">
                            @endif
                            <span class="font-semibold text-ink">{{ $t->client_name }}</span>
                        </figcaption>
                    </figure>
                @empty
                    {{-- Repli : aucun témoignage publié depuis /admin/temoignages. --}}
                    @foreach ($temoignagesFallback as $t)
                        <figure class="card-soft flex flex-col p-8">
                            <div class="flex gap-1 text-taupe">
                                @for ($i = 0; $i < $t['note']; $i++)
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 15.27 16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"/></svg>
                                @endfor
                            </div>
                            <blockquote class="mt-5 flex-1 font-serif text-xl leading-snug text-cocoa">« {{ $t['texte'] }} »</blockquote>
                            <figcaption class="mt-6 border-t border-ink/10 pt-4 text-sm">
                                <span class="font-semibold text-ink">{{ $t['nom'] }}</span>
                                <span class="block text-xs text-ink/50">{{ $t['prestation'] }}</span>
                            </figcaption>
                        </figure>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    {{-- ================= FAQ ================= --}}
    <section id="faq" class="bg-sand section-y">
        <div class="container-editorial max-w-3xl">
            <x-section-heading align="center" eyebrow="Conseils & clarté" title="Questions fréquentes." />

            <div class="mt-14 divide-y divide-ink/10 border-y border-ink/10" x-data="{ open: 0 }">
                @foreach ($faq as $i => $item)
                    <div>
                        <button type="button" class="flex w-full items-start justify-between gap-6 py-6 text-left"
                                @click="open === {{ $i }} ? open = null : open = {{ $i }}">
                            <span class="font-serif text-xl text-cocoa">{{ $item['q'] }}</span>
                            <svg class="mt-1 h-5 w-5 flex-none text-taupe transition-transform" :class="open === {{ $i }} && 'rotate-45'" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak>
                            <p class="pb-6 text-sm text-ink/70">{{ $item['r'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= CTA FINAL ================= --}}
    <section class="bg-cocoa text-cream">
        <div class="container-editorial section-y text-center">
            <p class="eyebrow text-taupe">Salon privé · Paris</p>
            <h2 class="display-2 mx-auto mt-5 max-w-2xl text-white text-balance">Prête pour une transformation soyeuse ?</h2>
            <p class="mx-auto mt-5 max-w-xl text-cream/70">
                Réservez votre expérience AylaLisse et découvrez une nouvelle relation avec vos cheveux.
            </p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('booking') }}" class="btn btn-light">Prendre rendez-vous</a>
                <a href="#resultats" class="btn btn-light">Découvrir nos résultats</a>
            </div>
        </div>
    </section>
</x-layouts.public>
