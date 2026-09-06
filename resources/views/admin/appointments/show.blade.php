@php
    // Les anciens slugs (formulaire 5 étapes) sont conservés ici pour que
    // l'historique reste lisible ; le formulaire actuel n'écrit plus que
    // courts/mi-longs/longs et les nouvelles clés de couleur.
    $hairLengthLabels = [
        'courts' => 'Cheveux courts', 'mi-longs' => 'Cheveux mi-longs', 'longs' => 'Cheveux longs',
        'epaules' => 'Épaules (ancien)', 'mi-dos' => 'Mi-dos (ancien)', 'bas-du-dos' => 'Bas du dos (ancien)',
    ];
    $textureLabels = ['ondules' => 'Ondulés', 'boucles' => 'Bouclés', 'tres-frises-crepus' => 'Très frisés / crépus'];
    $colorLabels = [
        'coloration' => 'Colorés',
        'decoloration-balayage' => 'Méchés / Balayage',
        'decoloration' => 'Décolorés',
        'autre' => 'Autre (voir précisions)',
        'precedent-lissage' => 'Précédent lissage (ancien)',
    ];
@endphp

<x-layouts.admin :title="'Rendez-vous '.$appointment->reference">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('admin.appointments.index') }}" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">← Tous les rendez-vous</a>
            <h1 class="mt-2 font-serif text-2xl text-cocoa">{{ $appointment->reference }}</h1>
        </div>
        <x-admin.status-badge :status="$appointment->status" class="text-sm" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card title="Rendez-vous">
                <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-ink/45">Prestation</dt><dd class="mt-1 font-medium">{{ $appointment->lissageService->name }}</dd></div>
                    <div><dt class="text-ink/45">Date</dt><dd class="mt-1 font-medium">{{ $appointment->appointment_date->translatedFormat('d F Y') }}</dd></div>
                    <div><dt class="text-ink/45">Heure</dt><dd class="mt-1 font-medium">{{ \Illuminate\Support\Carbon::parse($appointment->start_time)->format('H:i') }} — {{ \Illuminate\Support\Carbon::parse($appointment->end_time)->format('H:i') }}</dd></div>
                    <div><dt class="text-ink/45">Durée</dt><dd class="mt-1 font-medium">{{ $appointment->lissageService->duration_minutes }} min</dd></div>
                    <div><dt class="text-ink/45">Prix</dt><dd class="mt-1 font-medium">{{ number_format((float) $appointment->price, 2, ',', ' ') }} €</dd></div>
                    <div><dt class="text-ink/45">Acompte</dt><dd class="mt-1 font-medium">{{ number_format((float) $appointment->deposit_amount, 2, ',', ' ') }} €</dd></div>
                    <div><dt class="text-ink/45">Solde restant</dt><dd class="mt-1 font-medium">{{ number_format((float) $appointment->remaining_amount, 2, ',', ' ') }} €</dd></div>
                </dl>
            </x-admin.card>

            <x-admin.card title="Cliente">
                <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-ink/45">Prénom</dt><dd class="mt-1 font-medium">{{ $appointment->client->first_name ?: '—' }}</dd></div>
                    <div><dt class="text-ink/45">Nom</dt><dd class="mt-1 font-medium">{{ $appointment->client->last_name ?: '—' }}</dd></div>
                    @if (! $appointment->client->first_name && ! $appointment->client->last_name)
                        <div><dt class="text-ink/45">Nom complet (ancien format)</dt><dd class="mt-1 font-medium">{{ $appointment->client->full_name }}</dd></div>
                    @endif
                    <div><dt class="text-ink/45">Téléphone</dt><dd class="mt-1 font-medium">{{ $appointment->client->phone }}</dd></div>
                    <div><dt class="text-ink/45">E-mail</dt><dd class="mt-1 font-medium">{{ $appointment->client->email ?? '—' }}</dd></div>
                </dl>
                <a href="{{ route('admin.clients.show', $appointment->client) }}" class="mt-4 inline-block text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">Voir la fiche cliente →</a>
            </x-admin.card>

            <x-admin.card title="Diagnostic capillaire">
                <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                    <div><dt class="text-ink/45">Longueur des cheveux</dt><dd class="mt-1 font-medium">{{ $hairLengthLabels[$appointment->hair_length] ?? '—' }}</dd></div>
                    <div><dt class="text-ink/45">Couleur des cheveux</dt>
                        <dd class="mt-1 font-medium">
                            @forelse (($appointment->chemical_history ?? []) as $item)
                                <span class="mr-2 inline-block">{{ $colorLabels[$item] ?? $item }}</span>
                            @empty
                                Naturelle
                            @endforelse
                        </dd>
                    </div>
                    @if ($appointment->natural_texture)
                        <div><dt class="text-ink/45">Texture (ancien format)</dt><dd class="mt-1 font-medium">{{ $textureLabels[$appointment->natural_texture] ?? '—' }}</dd></div>
                    @endif
                    <div class="col-span-2 sm:col-span-3">
                        <dt class="text-ink/45">Précisions éventuelles</dt>
                        <dd class="mt-1 font-medium">{{ $appointment->hair_notes ?: '—' }}</dd>
                    </div>
                </dl>
            </x-admin.card>

            <x-admin.card title="Notes internes">
                <form method="POST" action="{{ route('admin.appointments.notes', $appointment) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <x-admin.textarea label="Notes visibles uniquement par l'équipe" name="admin_notes" :value="$appointment->admin_notes" rows="3" />
                    <button type="submit" class="admin-btn admin-btn-outline">Enregistrer les notes</button>
                </form>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card title="Actions">
                <div class="flex flex-col gap-2">
                    @foreach ($appointment->status->allowedTransitions() as $target)
                        <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}"
                            @if ($target->value === 'cancelled') onsubmit="return confirm('Confirmer l\'annulation de ce rendez-vous ?');" @endif
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $target->value }}">
                            <button type="submit" class="admin-btn w-full {{ $target->value === 'cancelled' ? 'admin-btn-danger' : 'admin-btn-primary' }}">
                                @switch($target->value)
                                    @case('confirmed') Confirmer @break
                                    @case('completed') Marquer terminé @break
                                    @case('cancelled') Annuler @break
                                    @case('no_show') Marquer absente @break
                                @endswitch
                            </button>
                        </form>
                    @endforeach

                    @if (empty($appointment->status->allowedTransitions()))
                        <p class="text-sm text-ink/45">Aucune action de statut disponible pour ce rendez-vous.</p>
                    @endif
                </div>
            </x-admin.card>

            <x-admin.card title="Reprogrammer">
                <form method="POST" action="{{ route('admin.appointments.reschedule', $appointment) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <x-admin.select
                        label="Prestation"
                        name="lissage_service_id"
                        :options="$services->pluck('name', 'id')"
                        :value="$appointment->lissage_service_id"
                    />
                    <x-admin.input label="Nouvelle date" name="appointment_date" type="date" :value="$appointment->appointment_date->toDateString()" required />
                    <x-admin.input label="Nouvelle heure" name="start_time" type="time" :value="\Illuminate\Support\Carbon::parse($appointment->start_time)->format('H:i')" required />

                    <button type="submit" class="admin-btn admin-btn-outline w-full">Reprogrammer</button>
                    <p class="text-xs text-ink/45">La disponibilité est revérifiée côté serveur avant toute modification.</p>
                </form>
            </x-admin.card>
        </div>
    </div>
</x-layouts.admin>
