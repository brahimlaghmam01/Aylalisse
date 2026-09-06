@php
    $service = $appointment->lissageService;
    $dateLabel = \Illuminate\Support\Str::ucfirst($appointment->appointment_date->translatedFormat('l j F Y'));
    $durationMinutes = $service->duration_minutes;
    $durationLabel = $durationMinutes % 60 === 0
        ? intdiv($durationMinutes, 60).'h'
        : intdiv($durationMinutes, 60).'h'.str_pad((string) ($durationMinutes % 60), 2, '0', STR_PAD_LEFT);
@endphp

<x-layouts.public>
    @section('title', 'Rendez-vous confirmé — AylaLisse')
    @section('noindex', '1')

    <section class="section-y">
        <div class="container-editorial max-w-2xl">
            <div class="text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-taupe/50 text-taupe">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </span>

                <p class="eyebrow mt-6">Demande enregistrée</p>
                <h1 class="display-1 mt-4 text-balance">Votre demande est bien enregistrée.</h1>
                <p class="mx-auto mt-6 max-w-lg text-ink/70">
                    Merci d'avoir choisi AylaLisse. Votre demande de rendez-vous a été enregistrée avec succès.
                    Nous vous contacterons prochainement pour confirmer votre rendez-vous.
                </p>
            </div>

            <div class="card-soft mt-12 p-8">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-ink/10 pb-6">
                    <div>
                        <p class="eyebrow">Référence</p>
                        <p class="mt-1 font-serif text-2xl text-cocoa">{{ $appointment->reference }}</p>
                    </div>
                    <span class="border border-taupe/40 px-3 py-1 text-[0.6875rem] font-semibold uppercase tracking-[0.14em] text-taupe">
                        Demande en attente de confirmation
                    </span>
                </div>

                <dl class="mt-6 space-y-4 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink/50">Prestation</dt>
                        <dd class="text-right font-medium text-ink">{{ $service->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink/50">Date</dt>
                        <dd class="text-right font-medium text-ink">{{ $dateLabel }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink/50">Horaire</dt>
                        <dd class="text-right font-medium text-ink">{{ \Illuminate\Support\Carbon::parse($appointment->start_time)->format('H:i') }} — {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->format('H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink/50">Durée</dt>
                        <dd class="text-right font-medium text-ink">{{ $durationLabel }} de rituel</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-t border-ink/10 pt-4">
                        <dt class="text-ink/50">Total prestation</dt>
                        <dd class="text-right font-medium text-ink">{{ $appointment->priceLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink/50">Acompte de réservation</dt>
                        <dd class="text-right font-serif text-lg text-cocoa">{{ $appointment->depositLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-ink/50">Solde le jour du rendez-vous</dt>
                        <dd class="text-right font-medium text-ink">{{ $appointment->remainingLabel() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="mt-10 flex flex-col justify-center gap-3 sm:flex-row">
                <a href="{{ route('home') }}" class="btn btn-primary">Retour à l'accueil</a>
                <a href="{{ route('home') }}#resultats" class="btn btn-outline">Découvrir nos résultats</a>
            </div>
        </div>
    </section>
</x-layouts.public>
