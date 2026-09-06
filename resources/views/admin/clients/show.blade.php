@php
    $hairLengthLabels = [
        'courts' => 'Cheveux courts', 'mi-longs' => 'Cheveux mi-longs', 'longs' => 'Cheveux longs',
        'epaules' => 'Épaules (ancien)', 'mi-dos' => 'Mi-dos (ancien)', 'bas-du-dos' => 'Bas du dos (ancien)',
    ];
    $textureLabels = ['ondules' => 'Ondulés', 'boucles' => 'Bouclés', 'tres-frises-crepus' => 'Très frisés / crépus'];
@endphp

<x-layouts.admin :title="$client->full_name">
    <a href="{{ route('admin.clients.index') }}" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">← Toutes les clientes</a>
    <h1 class="mb-8 mt-2 font-serif text-2xl text-cocoa">{{ $client->full_name }}</h1>

    {{-- Résumé financier --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="admin-card p-4">
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.08em] text-ink/45">Rendez-vous</p>
            <p class="mt-1.5 font-serif text-2xl text-cocoa">{{ $financials['total_appointments'] }}</p>
        </div>
        <div class="admin-card p-4">
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.08em] text-ink/45">Prestations terminées</p>
            <p class="mt-1.5 font-serif text-2xl text-cocoa">{{ $financials['completed_count'] }}</p>
        </div>
        <div class="admin-card p-4">
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.08em] text-ink/45">Valeur des prestations terminées</p>
            <p class="mt-1.5 font-serif text-2xl text-cocoa"><x-admin.money :value="$financials['completed_value']" /></p>
        </div>
        <div class="admin-card p-4">
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.08em] text-ink/45">Réellement encaissé</p>
            <p class="mt-1.5 font-serif text-2xl text-emerald-700"><x-admin.money :value="$financials['collected']" /></p>
        </div>
        <div class="admin-card p-4">
            <p class="text-[0.625rem] font-semibold uppercase tracking-[0.08em] text-ink/45">Restant à encaisser</p>
            <p class="mt-1.5 font-serif text-2xl text-cocoa"><x-admin.money :value="$financials['outstanding']" /></p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-admin.card title="Informations générales">
            <dl class="space-y-3 text-sm">
                @if ($client->first_name || $client->last_name)
                    <div><dt class="text-ink/45">Prénom</dt><dd class="mt-1 font-medium">{{ $client->first_name ?: '—' }}</dd></div>
                    <div><dt class="text-ink/45">Nom</dt><dd class="mt-1 font-medium">{{ $client->last_name ?: '—' }}</dd></div>
                @endif
                <div><dt class="text-ink/45">Téléphone</dt><dd class="mt-1 font-medium">{{ $client->phone }}</dd></div>
                <div><dt class="text-ink/45">E-mail</dt><dd class="mt-1 font-medium">{{ $client->email ?? '—' }}</dd></div>
                <div><dt class="text-ink/45">Nombre total de rendez-vous</dt><dd class="mt-1 font-medium">{{ $client->appointments->count() }}</dd></div>
            </dl>
        </x-admin.card>

        <div class="admin-card lg:col-span-2">
            <div class="border-b border-nude/30 px-6 py-4">
                <h2 class="font-serif text-lg text-cocoa">Historique financier des rendez-vous</h2>
            </div>
            @if ($client->appointments->isEmpty())
                <p class="p-6 text-sm text-ink/50">Aucun rendez-vous pour l'instant.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="admin-table w-full">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Prestation</th>
                                <th class="text-right">Prix</th>
                                <th class="text-right">Acompte</th>
                                <th class="text-right">Solde</th>
                                <th>Encaissement</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($client->appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_date->translatedFormat('d/m/Y') }}</td>
                                    <td>{{ $appointment->lissageService->name }}</td>
                                    <td class="text-right">{{ $appointment->priceLabel() }}</td>
                                    <td class="text-right">{{ $appointment->depositLabel() }}</td>
                                    <td class="text-right">{{ $appointment->remainingLabel() }}</td>
                                    <td class="whitespace-nowrap text-xs">
                                        <span class="{{ $appointment->isDepositPaid() ? 'text-emerald-700' : 'text-ink/40' }}">Acompte {{ $appointment->isDepositPaid() ? '✓' : '—' }}</span><br>
                                        <span class="{{ $appointment->isBalancePaid() ? 'text-emerald-700' : 'text-ink/40' }}">Solde {{ $appointment->isBalancePaid() ? '✓' : '—' }}</span>
                                    </td>
                                    <td><x-admin.status-badge :status="$appointment->status" /></td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.appointments.show', $appointment) }}" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">Voir</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.admin>
