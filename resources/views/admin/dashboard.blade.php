<x-layouts.admin title="Tableau de bord">
    <x-admin.page-header title="Tableau de bord" subtitle="Vue d'ensemble de l'activité AylaLisse." />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="admin-card p-5">
            <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.1em] text-ink/50">Rendez-vous aujourd'hui</p>
            <p class="mt-2 font-serif text-3xl text-cocoa">{{ $stats['today'] }}</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.1em] text-ink/50">En attente</p>
            <p class="mt-2 font-serif text-3xl text-cocoa">{{ $stats['pending'] }}</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.1em] text-ink/50">Confirmés</p>
            <p class="mt-2 font-serif text-3xl text-cocoa">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="admin-card p-5">
            <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.1em] text-ink/50">Total des clientes</p>
            <p class="mt-2 font-serif text-3xl text-cocoa">{{ $stats['clients'] }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-3">
        <div class="admin-card lg:col-span-2">
            <div class="border-b border-nude/30 px-6 py-4">
                <h2 class="font-serif text-lg text-cocoa">Les rendez-vous du jour</h2>
            </div>
            @if ($todaysAppointments->isEmpty())
                <p class="p-6 text-sm text-ink/50">Aucun rendez-vous prévu aujourd'hui.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="admin-table w-full">
                        <thead>
                            <tr>
                                <th>Heure</th>
                                <th>Cliente</th>
                                <th>Prestation</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($todaysAppointments as $appointment)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($appointment->start_time)->format('H:i') }}</td>
                                    <td>{{ $appointment->client->full_name }}</td>
                                    <td>{{ $appointment->lissageService->name }}</td>
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

        <div class="admin-card">
            <div class="border-b border-nude/30 px-6 py-4">
                <h2 class="font-serif text-lg text-cocoa">Demandes en attente</h2>
            </div>
            @if ($pendingRequests->isEmpty())
                <p class="p-6 text-sm text-ink/50">Aucune demande en attente.</p>
            @else
                <ul class="divide-y divide-ink/5">
                    @foreach ($pendingRequests as $appointment)
                        <li class="flex items-center justify-between gap-3 px-6 py-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-ink">{{ $appointment->client->full_name }}</p>
                                <p class="mt-0.5 text-xs text-ink/50">
                                    {{ $appointment->appointment_date->translatedFormat('d M') }} · {{ \Illuminate\Support\Carbon::parse($appointment->start_time)->format('H:i') }} · {{ $appointment->lissageService->name }}
                                </p>
                            </div>
                            <div class="flex flex-none items-center gap-2">
                                <form method="POST" action="{{ route('admin.appointments.status', $appointment) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-emerald-700 hover:text-emerald-900">Confirmer</button>
                                </form>
                                <a href="{{ route('admin.appointments.show', $appointment) }}" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">Voir</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts.admin>
