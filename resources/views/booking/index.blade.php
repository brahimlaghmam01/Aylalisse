<x-layouts.public :hide-floating-cta="true" whatsapp-message="Bonjour AylaLisse, j'ai une question concernant ma réservation.">
    @section('title', 'Réservation — AylaLisse')
    @section('meta_description', 'Réservez votre rituel de transformation soyeuse chez AylaLisse en un seul formulaire simple et rapide.')

    <section class="section-y" x-data="bookingFlow(@js($servicesPayload))">
        <div class="container-editorial">
            {{-- En-tête --}}
            <div class="max-w-2xl">
                <p class="eyebrow">Réservation AylaLisse</p>
                <h1 class="display-1 mt-4 text-balance">Votre rituel de transformation soyeuse</h1>
                <p class="mt-4 text-ink/60">Un formulaire simple pour réserver votre lissage — quelques informations, une date, et c'est confirmé.</p>
            </div>

            {{-- Erreur globale (créneau devenu indisponible, erreur serveur...) --}}
            <div x-show="submitError" x-cloak class="mt-8 max-w-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" x-text="submitError"></div>

            <div class="mt-10 grid gap-12 lg:grid-cols-[1fr_360px] lg:items-start">
                {{-- Colonne principale : formulaire --}}
                <div class="space-y-12">
                    {{-- SECTION 1 — PRESTATION --}}
                    <div>
                        <h2 class="display-3">Choisissez votre lissage</h2>
                        <p class="mt-2 text-sm text-ink/60">Sélectionnez l'expérience qui correspond à vos cheveux.</p>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2" role="radiogroup" aria-label="Expérience de lissage">
                            <template x-for="service in services" :key="service.id">
                                <button
                                    type="button"
                                    role="radio"
                                    :aria-checked="selectedServiceId === service.id"
                                    @click="selectService(service.id)"
                                    class="card-soft flex flex-col items-start p-6 text-left"
                                    :class="selectedServiceId === service.id ? 'border-cocoa ring-1 ring-cocoa bg-sand/30' : ''"
                                >
                                    <div class="flex w-full items-start justify-between gap-3">
                                        <h3 class="display-3 text-xl" x-text="service.name"></h3>
                                        <span
                                            class="flex h-5 w-5 flex-none items-center justify-center rounded-full border"
                                            :class="selectedServiceId === service.id ? 'border-cocoa bg-cocoa' : 'border-ink/20'"
                                        >
                                            <svg x-show="selectedServiceId === service.id" class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                                        </span>
                                    </div>

                                    <p class="mt-2 text-sm text-ink/65" x-text="service.short_description"></p>

                                    <div class="mt-5 flex w-full items-baseline justify-between border-t border-ink/10 pt-4">
                                        <span class="text-xs text-ink/50" x-text="service.duration_label + ' de rituel'"></span>
                                        <span class="font-serif text-lg text-cocoa" x-text="service.price_label"></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- SECTION 2 — CHEVEUX --}}
                    <div>
                        <h2 class="display-3">Vos cheveux</h2>
                        <p class="mt-2 text-sm text-ink/60">Quelques informations pour préparer votre rituel.</p>

                        <div class="mt-6 card-soft p-6 sm:p-8">
                            <p class="text-xs font-semibold uppercase tracking-wide text-ink/60">
                                Longueur des cheveux<span x-show="needsHairLength"> *</span>
                            </p>
                            <p x-show="needsHairLength" class="mt-1 text-xs text-taupe">Le tarif de votre lissage dépend de la longueur.</p>
                            <div class="mt-3 grid grid-cols-3 gap-3" role="radiogroup" aria-label="Longueur des cheveux">
                                <template x-for="option in hairLengths" :key="option.value">
                                    <button
                                        type="button" role="radio" :aria-checked="hairLength === option.value"
                                        @click="selectHairLength(option.value)"
                                        class="flex min-h-[3rem] flex-col items-center justify-center gap-0.5 border p-3 text-center text-sm transition-colors"
                                        :class="hairLength === option.value ? 'border-cocoa bg-sand/30 text-cocoa' : 'border-nude/40 text-ink/70 hover:border-taupe'"
                                    >
                                        <span x-text="option.label"></span>
                                        <span
                                            x-show="selectedService && selectedService.has_length_pricing && selectedService.length_prices[option.value] !== undefined"
                                            class="text-xs text-taupe"
                                            x-text="selectedService ? money(selectedService.length_prices[option.value]) : ''"
                                        ></span>
                                    </button>
                                </template>
                            </div>
                            <p x-show="errors.hair_length" x-cloak x-text="errors.hair_length" class="mt-2 text-xs text-red-600"></p>

                            <p class="mt-7 text-xs font-semibold uppercase tracking-wide text-ink/60">Couleur des cheveux</p>
                            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <template x-for="option in hairColors" :key="option.value">
                                    <button
                                        type="button" role="radio" :aria-checked="hairColor === option.value"
                                        @click="selectHairColor(option.value)"
                                        class="border p-3 text-center text-sm transition-colors"
                                        :class="hairColor === option.value ? 'border-cocoa bg-sand/30 text-cocoa' : 'border-nude/40 text-ink/70 hover:border-taupe'"
                                        x-text="option.label"
                                    ></button>
                                </template>
                            </div>

                            <div class="mt-5" x-show="hairColor === 'autre'" x-cloak>
                                <label for="hair_color_details" class="text-xs font-semibold uppercase tracking-wide text-ink/60">Précisions sur vos cheveux</label>
                                <textarea
                                    id="hair_color_details" x-model="hairColorDetails" rows="2"
                                    placeholder="Décrivez brièvement la couleur ou les traitements de vos cheveux."
                                    class="mt-2 w-full border border-nude/50 bg-cream px-4 py-3 text-sm focus:border-cocoa focus:outline-none"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3 — DATE & CRÉNEAU --}}
                    <div id="creneaux">
                        <h2 class="display-3">Choisissez la date et l'heure</h2>
                        <p class="mt-2 text-sm text-ink/60">Nous respectons la durée nécessaire à chaque transformation.</p>

                        <template x-if="!selectedService">
                            <p class="mt-6 border border-dashed border-nude/50 p-6 text-center text-sm text-ink/50">Choisissez d'abord un lissage ci-dessus pour voir les dates disponibles.</p>
                        </template>

                        <template x-if="selectedService">
                            <div class="mt-6 card-soft p-6">
                                <div class="flex items-center justify-between">
                                    <button type="button" @click="changeMonth(-1)" :disabled="!canGoPrevMonth" class="flex h-9 w-9 items-center justify-center border border-ink/15 text-cocoa disabled:opacity-25" aria-label="Mois précédent">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/></svg>
                                    </button>
                                    <p class="font-serif text-lg text-cocoa" x-text="calendarMonthLabel"></p>
                                    <button type="button" @click="changeMonth(1)" :disabled="!canGoNextMonth" class="flex h-9 w-9 items-center justify-center border border-ink/15 text-cocoa disabled:opacity-25" aria-label="Mois suivant">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                                    </button>
                                </div>

                                <div class="mt-6 grid grid-cols-7 gap-1 text-center text-[0.625rem] font-semibold uppercase tracking-wider text-ink/40">
                                    <template x-for="label in weekdayLabels" :key="label"><span x-text="label"></span></template>
                                </div>

                                <div class="relative mt-2 grid grid-cols-7 gap-1.5" aria-live="polite">
                                    <div x-show="calendarLoading" x-cloak class="absolute inset-0 z-10 flex items-center justify-center bg-cream/80">
                                        <span class="text-[0.6875rem] font-semibold uppercase tracking-[0.16em] text-taupe">Chargement du calendrier…</span>
                                    </div>

                                    <template x-for="n in calendarLeadingBlanks" :key="'blank-' + n"><span></span></template>

                                    <template x-for="day in calendarDays" :key="day.date">
                                        <button
                                            type="button"
                                            @click="pickDate(day)"
                                            :disabled="!day.bookable"
                                            :aria-pressed="selectedDate === day.date"
                                            class="aspect-square text-sm transition-colors"
                                            :class="{
                                                'bg-cocoa text-white': selectedDate === day.date,
                                                'border border-nude/40 text-ink hover:border-taupe': day.bookable && selectedDate !== day.date,
                                                'text-ink/25 cursor-not-allowed': !day.bookable,
                                            }"
                                            x-text="dayNumber(day.date)"
                                        ></button>
                                    </template>
                                </div>

                                <p x-show="calendarError" x-cloak x-text="calendarError" class="mt-4 text-sm text-red-700"></p>

                                {{-- Créneaux --}}
                                <div x-show="selectedDate" x-cloak class="mt-8 border-t border-ink/10 pt-6">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-ink/60" x-text="selectedDate ? formattedDate(selectedDate) : ''"></p>

                                    <p x-show="slotsLoading" class="mt-4 text-[0.6875rem] font-semibold uppercase tracking-[0.16em] text-taupe">Recherche des disponibilités…</p>
                                    <p x-show="slotsError" x-cloak x-text="slotsError" class="mt-4 text-sm text-red-700"></p>
                                    <p x-show="!slotsLoading && !slotsError && slots.length === 0" class="mt-4 text-sm text-ink/50">Aucun créneau pour cette date.</p>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2" x-show="!slotsLoading">
                                        <template x-for="slot in slots" :key="slot.start">
                                            <button
                                                type="button"
                                                @click="pickSlot(slot)"
                                                :disabled="!slot.available"
                                                class="flex items-center justify-between border p-4 text-left"
                                                :class="{
                                                    'border-cocoa bg-sand/30': selectedSlot && selectedSlot.start === slot.start,
                                                    'border-nude/40 hover:border-taupe': slot.available && !(selectedSlot && selectedSlot.start === slot.start),
                                                    'border-ink/10 text-ink/30 cursor-not-allowed': !slot.available,
                                                }"
                                            >
                                                <span class="font-serif text-lg" x-text="slot.start + ' — ' + slot.end"></span>
                                                <span
                                                    class="text-[0.625rem] font-semibold uppercase tracking-[0.14em]"
                                                    :class="slot.available ? 'text-taupe' : 'text-ink/30'"
                                                    x-text="slot.available ? 'Disponible' : 'Complet'"
                                                ></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- SECTION 4 — COORDONNÉES --}}
                    <div>
                        <h2 class="display-3">Vos informations</h2>
                        <p class="mt-2 text-sm text-ink/60">Pour confirmer votre créneau et vous accueillir dans les meilleures conditions.</p>

                        <div class="mt-6 card-soft space-y-5 p-6 sm:p-8">
                            <div class="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wide text-ink/60" for="first_name">Prénom *</label>
                                    <input
                                        id="first_name" type="text" x-model="profile.first_name" autocomplete="given-name"
                                        class="mt-2 w-full border bg-cream px-4 py-3 text-sm focus:border-cocoa focus:outline-none"
                                        :class="errors.first_name ? 'border-red-400' : 'border-nude/50'"
                                    >
                                    <p x-show="errors.first_name" x-cloak x-text="errors.first_name" class="mt-1 text-xs text-red-600"></p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wide text-ink/60" for="last_name">Nom *</label>
                                    <input
                                        id="last_name" type="text" x-model="profile.last_name" autocomplete="family-name"
                                        class="mt-2 w-full border bg-cream px-4 py-3 text-sm focus:border-cocoa focus:outline-none"
                                        :class="errors.last_name ? 'border-red-400' : 'border-nude/50'"
                                    >
                                    <p x-show="errors.last_name" x-cloak x-text="errors.last_name" class="mt-1 text-xs text-red-600"></p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wide text-ink/60" for="phone">Téléphone *</label>
                                    <input
                                        id="phone" type="tel" x-model="profile.phone" autocomplete="tel"
                                        class="mt-2 w-full border bg-cream px-4 py-3 text-sm focus:border-cocoa focus:outline-none"
                                        :class="errors.phone ? 'border-red-400' : 'border-nude/50'"
                                    >
                                    <p x-show="errors.phone" x-cloak x-text="errors.phone" class="mt-1 text-xs text-red-600"></p>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold uppercase tracking-wide text-ink/60" for="email">Adresse e-mail *</label>
                                    <input
                                        id="email" type="email" x-model="profile.email" autocomplete="email"
                                        class="mt-2 w-full border bg-cream px-4 py-3 text-sm focus:border-cocoa focus:outline-none"
                                        :class="errors.email ? 'border-red-400' : 'border-nude/50'"
                                    >
                                    <p x-show="errors.email" x-cloak x-text="errors.email" class="mt-1 text-xs text-red-600"></p>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-ink/60" for="message">Message / remarque (facultatif)</label>
                                <textarea
                                    id="message" x-model="profile.message" rows="3"
                                    placeholder="Une précision à nous transmettre avant votre venue ?"
                                    class="mt-2 w-full border border-nude/50 bg-cream px-4 py-3 text-sm focus:border-cocoa focus:outline-none"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Résumé compact — mobile uniquement, juste avant le bouton final --}}
                    <div class="card-soft p-6 lg:hidden">
                        @include('booking.partials.summary-content')
                    </div>

                    {{-- BOUTON FINAL --}}
                    <div>
                        <button type="button" @click="submit()" :disabled="submitting" class="btn btn-primary w-full disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">
                            <span x-show="!submitting">Valider ma réservation</span>
                            <span x-show="submitting" x-cloak>Confirmation…</span>
                        </button>
                        <p class="mt-3 text-xs text-ink/45">Votre créneau sera vérifié une dernière fois avant confirmation.</p>
                    </div>
                </div>

                {{-- Colonne secondaire : résumé sticky (bureau) --}}
                <aside class="hidden lg:sticky lg:top-28 lg:block lg:self-start card-soft p-7">
                    @include('booking.partials.summary-content')
                </aside>
            </div>
        </div>
    </section>
</x-layouts.public>
