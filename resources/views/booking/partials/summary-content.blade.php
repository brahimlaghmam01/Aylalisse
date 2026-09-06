{{-- Résumé de réservation — panneau sticky bureau + bloc compact mobile.
     Purement affiché à partir de l'état Alpine ; aucun calcul de
     disponibilité ici. --}}

<p class="eyebrow">Votre réservation</p>

<div class="mt-6 space-y-6">
    {{-- PRESTATION --}}
    <div>
        <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.18em] text-taupe">Lissage</p>
        <template x-if="selectedService">
            <div class="mt-2">
                <p class="font-serif text-xl text-cocoa" x-text="selectedService.name"></p>
                <p class="mt-1 text-xs text-ink/55" x-text="selectedService.duration_label + ' de rituel'"></p>
            </div>
        </template>
        <p class="mt-2 text-sm text-ink/40" x-show="!selectedService">Aucune expérience sélectionnée</p>
    </div>

    <div class="hairline"></div>

    {{-- CHEVEUX --}}
    <div>
        <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.18em] text-taupe">Vos cheveux</p>
        <template x-if="hairLength || hairColor">
            <ul class="mt-2 space-y-1 text-sm text-ink/70">
                <li x-show="hairLength" x-text="'Longueur : ' + hairLengthLabel()"></li>
                <li x-show="hairColor" x-text="'Couleur : ' + hairColorLabel()"></li>
            </ul>
        </template>
        <p class="mt-2 text-sm text-ink/40" x-show="!hairLength && !hairColor">À renseigner ci-dessous</p>
    </div>

    <div class="hairline"></div>

    {{-- DATE & HORAIRE --}}
    <div>
        <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.18em] text-taupe">Date &amp; horaire</p>
        <template x-if="selectedDate && selectedSlot">
            <div class="mt-2">
                <p class="font-serif text-lg text-cocoa" x-text="formattedDate(selectedDate)"></p>
                <p class="mt-1 text-xs text-ink/55" x-text="selectedSlot.start + ' — ' + selectedSlot.end"></p>
            </div>
        </template>
        <template x-if="selectedDate && !selectedSlot">
            <p class="mt-2 text-sm text-ink/55" x-text="formattedDate(selectedDate) + ' · créneau à choisir'"></p>
        </template>
        <p class="mt-2 text-sm text-ink/40" x-show="!selectedDate">Date et créneau à choisir</p>
    </div>

    <div class="hairline"></div>

    {{-- TARIFICATION --}}
    <div>
        <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.18em] text-taupe">Tarification</p>
        <template x-if="selectedService">
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex items-baseline justify-between">
                    <dt class="text-ink/60">Total prestation</dt>
                    <dd class="font-serif text-base text-cocoa" x-text="selectedService.price_label"></dd>
                </div>
                <div class="flex items-baseline justify-between">
                    <dt class="text-ink/60">Acompte de réservation</dt>
                    <dd class="text-ink/80" x-text="selectedService.deposit_label"></dd>
                </div>
                <div class="flex items-baseline justify-between border-t border-ink/10 pt-2">
                    <dt class="text-ink/60">Solde le jour du rendez-vous</dt>
                    <dd class="text-ink/80" x-text="remainingAmountLabel"></dd>
                </div>
            </dl>
        </template>
        <p class="mt-2 text-sm text-ink/40" x-show="!selectedService">—</p>
    </div>
</div>
