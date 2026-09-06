<x-layouts.admin title="Tableau de bord">
    <x-admin.page-header title="Tableau de bord" subtitle="Vue d'ensemble de l'activité et des revenus AylaLisse." />

    {{-- ============ CHIFFRE D'AFFAIRES ============ --}}
    <section class="mb-10">
        <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-serif text-lg text-cocoa">Chiffre d'affaires</h2>
                <p class="text-xs text-ink/50">{{ $revenue->periodLabel() }}</p>
            </div>

            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-end gap-2"
                  x-data="{ period: '{{ $period }}' }">
                <div>
                    <label for="period" class="sr-only">Période</label>
                    <select id="period" name="period" x-model="period" @change="if (period !== 'custom') $el.form.submit()"
                            class="admin-input py-1.5 text-xs">
                        @foreach ($periodOptions as $value => $label)
                            <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <template x-if="period === 'custom'">
                    <div class="flex items-end gap-2">
                        <input type="date" name="from" value="{{ $customFrom }}" class="admin-input py-1.5 text-xs" aria-label="Du">
                        <input type="date" name="to" value="{{ $customTo }}" class="admin-input py-1.5 text-xs" aria-label="Au">
                        <button type="submit" class="admin-btn admin-btn-outline py-1.5">Appliquer</button>
                    </div>
                </template>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="admin-card p-5">
                <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.1em] text-ink/50">Chiffre d'affaires</p>
                <p class="mt-2 font-serif text-3xl text-cocoa"><x-admin.money :value="$revenue->revenue()" /></p>
                <p class="mt-1 text-xs text-ink/45">{{ $revenue->completedCount() }} prestation{{ $revenue->completedCount() > 1 ? 's' : '' }} terminée{{ $revenue->completedCount() > 1 ? 's' : '' }} · prix historiques</p>
            </div>
            <div class="admin-card p-5">
                <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.1em] text-ink/50">Encaissé</p>
                <p class="mt-2 font-serif text-3xl text-cocoa"><x-admin.money :value="$revenue->collected()" /></p>
                <p class="mt-1 text-xs text-ink/45">Acomptes + soldes cochés « payé »</p>
            </div>
            <div class="admin-card p-5">
                <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.1em] text-ink/50">À encaisser</p>
                <p class="mt-2 font-serif text-3xl text-cocoa"><x-admin.money :value="$revenue->outstanding()" /></p>
                <p class="mt-1 text-xs text-ink/45">Restant dû sur les prestations terminées</p>
            </div>
            <div class="admin-card p-5">
                <p class="text-[0.6875rem] font-semibold uppercase tracking-[0.1em] text-ink/50">Acomptes enregistrés</p>
                <p class="mt-2 font-serif text-3xl text-cocoa"><x-admin.money :value="$revenue->deposits()" /></p>
                <p class="mt-1 text-xs text-ink/45">Total prévu, paiement non garanti</p>
            </div>
        </div>

        <p class="mt-3 text-xs text-ink/45">
            Le paiement du solde en salon se coche manuellement sur chaque fiche de rendez-vous : un rendez-vous « terminé » n'est pas considéré comme encaissé tant que le solde n'a pas été marqué payé.
        </p>

        {{-- Évolution mensuelle --}}
        @php $series = $revenue->monthlySeries(); $maxRevenue = max(array_column($series, 'revenue')) ?: 1; @endphp
        @if (array_sum(array_column($series, 'revenue')) > 0)
            <div class="admin-card mt-6 p-6">
                <h3 class="text-[0.6875rem] font-semibold uppercase tracking-[0.1em] text-ink/50">Évolution du chiffre d'affaires — 12 derniers mois</h3>
                <div class="mt-5 flex items-end gap-2 overflow-x-auto" style="height: 160px;">
                    @foreach ($series as $point)
                        <div class="flex min-w-[36px] flex-1 flex-col items-center justify-end gap-2" title="{{ $point['label'] }} — {{ \App\Support\Money::eurPrecise($point['revenue']) }}">
                            <span class="text-[0.625rem] text-ink/50">{{ $point['revenue'] > 0 ? number_format($point['revenue'], 0, ',', ' ') : '' }}</span>
                            <div class="w-full bg-cocoa/85" style="height: {{ max(2, round(($point['revenue'] / $maxRevenue) * 110)) }}px;"></div>
                            <span class="text-[0.625rem] uppercase tracking-wide text-ink/45">{{ $point['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Revenus par prestation --}}
        @php $byService = $revenue->byService(); @endphp
        @if (! empty($byService))
            <div class="admin-card mt-6 overflow-x-auto">
                <div class="border-b border-nude/30 px-6 py-4">
                    <h3 class="font-serif text-base text-cocoa">Revenus par prestation</h3>
                </div>
                <table class="admin-table w-full">
                    <thead>
                        <tr><th>Prestation</th><th>Prestations terminées</th><th class="text-right">Revenus</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($byService as $row)
                            <tr>
                                <td class="font-medium">{{ $row['name'] }}</td>
                                <td>{{ $row['count'] }}</td>
                                <td class="text-right"><x-admin.money :value="$row['revenue']" precise /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    {{-- ============ ACTIVITÉ ============ --}}
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
