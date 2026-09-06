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
                <h2 class="font-serif text-lg text-cocoa">Historique des rendez-vous</h2>
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
                                <th>Statut</th>
                                <th>Prix</th>
                                <th>Diagnostic</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($client->appointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_date->translatedFormat('d F Y') }}</td>
                                    <td>{{ $appointment->lissageService->name }}</td>
                                    <td><x-admin.status-badge :status="$appointment->status" /></td>
                                    <td>{{ number_format((float) $appointment->price, 2, ',', ' ') }} €</td>
                                    <td class="text-xs text-ink/60">
                                        {{ $hairLengthLabels[$appointment->hair_length] ?? '' }}
                                        @if ($appointment->hair_length && $appointment->natural_texture) · @endif
                                        {{ $textureLabels[$appointment->natural_texture] ?? '' }}
                                    </td>
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
