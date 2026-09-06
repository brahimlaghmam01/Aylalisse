<x-layouts.admin title="Rendez-vous">
    <x-admin.page-header title="Gestion des rendez-vous" subtitle="Rechercher, filtrer et gérer l'ensemble des réservations." />

    <form method="GET" class="admin-card mb-6 grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <label for="q" class="admin-label">Cliente ou téléphone</label>
            <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom ou téléphone" class="admin-input">
        </div>
        <div>
            <label for="date" class="admin-label">Date</label>
            <input type="date" id="date" name="date" value="{{ $filters['date'] ?? '' }}" class="admin-input">
        </div>
        <div>
            <label for="status" class="admin-label">Statut</label>
            <select id="status" name="status" class="admin-input">
                <option value="">Tous les statuts</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? null) === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="service" class="admin-label">Prestation</label>
            <select id="service" name="service" class="admin-input">
                <option value="">Toutes</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected((string) ($filters['service'] ?? '') === (string) $service->id)>{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-3 lg:col-span-5">
            <button type="submit" class="admin-btn admin-btn-primary">Filtrer</button>
            <a href="{{ route('admin.appointments.index') }}" class="admin-btn admin-btn-outline">Réinitialiser</a>
        </div>
    </form>

    <div class="admin-card overflow-x-auto">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Cliente</th>
                    <th>Téléphone</th>
                    <th>Prestation</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($appointments as $appointment)
                    <tr>
                        <td class="font-mono text-xs">{{ $appointment->reference }}</td>
                        <td>{{ $appointment->client->full_name }}</td>
                        <td>{{ $appointment->client->phone }}</td>
                        <td>{{ $appointment->lissageService->name }}</td>
                        <td>{{ $appointment->appointment_date->translatedFormat('d F Y') }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($appointment->start_time)->format('H:i') }}</td>
                        <td><x-admin.status-badge :status="$appointment->status" /></td>
                        <td class="text-right">
                            <a href="{{ route('admin.appointments.show', $appointment) }}" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-sm text-ink/50">Aucun rendez-vous ne correspond à ces critères.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $appointments->links() }}</div>
</x-layouts.admin>
