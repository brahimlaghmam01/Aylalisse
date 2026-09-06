<x-layouts.admin title="Clientes">
    <x-admin.page-header title="Clientes" subtitle="Toutes les clientes ayant réservé une expérience AylaLisse." />

    <form method="GET" class="admin-card mb-6 flex flex-wrap items-end gap-4 p-5">
        <div class="flex-1" style="min-width: 240px;">
            <label for="q" class="admin-label">Nom, téléphone ou e-mail</label>
            <input type="text" id="q" name="q" value="{{ $q }}" placeholder="Rechercher une cliente" class="admin-input">
        </div>
        <button type="submit" class="admin-btn admin-btn-primary">Rechercher</button>
        @if ($q)
            <a href="{{ route('admin.clients.index') }}" class="admin-btn admin-btn-outline">Réinitialiser</a>
        @endif
    </form>

    <div class="admin-card overflow-x-auto">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>E-mail</th>
                    <th>Rendez-vous</th>
                    <th>Dernier rendez-vous</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td class="font-medium">{{ $client->full_name }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>{{ $client->email ?? '—' }}</td>
                        <td>{{ $client->appointments_count }}</td>
                        <td>{{ $client->appointments_max_appointment_date ? \Illuminate\Support\Carbon::parse($client->appointments_max_appointment_date)->translatedFormat('d F Y') : '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.clients.show', $client) }}" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">Voir la fiche</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-sm text-ink/50">Aucune cliente trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $clients->links() }}</div>
</x-layouts.admin>
