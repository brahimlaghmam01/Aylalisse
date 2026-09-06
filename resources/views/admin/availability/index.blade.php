<x-layouts.admin title="Disponibilités">
    <x-admin.page-header title="Disponibilités" subtitle="Ces réglages pilotent directement le moteur de réservation public — tout changement est immédiat." />

    <x-admin.card title="Horaires hebdomadaires">
        <div class="space-y-3">
            @foreach ($businessHours as $hour)
                <form
                    method="POST"
                    action="{{ route('admin.availability.hours.update', $hour) }}"
                    class="grid items-end gap-3 border border-nude/30 p-4 sm:grid-cols-[140px_120px_1fr_1fr_auto]"
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="day_of_week" value="{{ $hour->day_of_week }}">

                    <p class="font-medium text-ink">{{ $hour->label() }}</p>

                    <label class="flex items-center gap-2 text-sm text-ink/70">
                        <input type="checkbox" name="is_open" value="1" class="h-4 w-4 accent-cocoa" @checked($hour->is_open)>
                        Ouvert
                    </label>

                    <div>
                        <label class="admin-label">Ouverture</label>
                        <input type="time" name="open_time" value="{{ $hour->open_time ? \Illuminate\Support\Carbon::parse($hour->open_time)->format('H:i') : '' }}" class="admin-input">
                    </div>
                    <div>
                        <label class="admin-label">Fermeture</label>
                        <input type="time" name="close_time" value="{{ $hour->close_time ? \Illuminate\Support\Carbon::parse($hour->close_time)->format('H:i') : '' }}" class="admin-input">
                    </div>

                    <button type="submit" class="admin-btn admin-btn-outline">Enregistrer</button>
                </form>
            @endforeach
        </div>
        @error('open_time') <p class="mt-3 text-xs text-red-600">{{ $message }}</p> @enderror
        @error('close_time') <p class="mt-3 text-xs text-red-600">{{ $message }}</p> @enderror
    </x-admin.card>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-admin.card title="Dates bloquées">
            <form method="POST" action="{{ route('admin.availability.blocked-dates.store') }}" class="mb-6 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                @csrf
                <x-admin.input label="Date" name="date" type="date" required />
                <x-admin.input label="Motif (facultatif)" name="reason" />
                <div class="flex items-end"><button type="submit" class="admin-btn admin-btn-primary w-full">Bloquer</button></div>
            </form>

            @if ($blockedDates->isEmpty())
                <p class="text-sm text-ink/50">Aucune date bloquée.</p>
            @else
                <ul class="divide-y divide-ink/5">
                    @foreach ($blockedDates as $date)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <div>
                                <p class="text-sm font-medium">{{ $date->date->translatedFormat('d F Y') }}</p>
                                @if ($date->reason)<p class="text-xs text-ink/50">{{ $date->reason }}</p>@endif
                            </div>
                            <form method="POST" action="{{ route('admin.availability.blocked-dates.destroy', $date) }}" onsubmit="return confirm('Débloquer cette date ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-red-600 hover:text-red-800">Supprimer</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>

        <x-admin.card title="Plages horaires bloquées">
            <form method="POST" action="{{ route('admin.availability.blocked-ranges.store') }}" class="mb-6 space-y-3">
                @csrf
                <x-admin.input label="Date" name="date" type="date" required />
                <div class="grid grid-cols-2 gap-3">
                    <x-admin.input label="Début" name="start_time" type="time" required />
                    <x-admin.input label="Fin" name="end_time" type="time" required />
                </div>
                <x-admin.input label="Motif (facultatif)" name="reason" placeholder="Ex. Pause exceptionnelle, formation…" />
                <button type="submit" class="admin-btn admin-btn-primary w-full">Bloquer la plage</button>
            </form>

            @if ($blockedTimeRanges->isEmpty())
                <p class="text-sm text-ink/50">Aucune plage horaire bloquée.</p>
            @else
                <ul class="divide-y divide-ink/5">
                    @foreach ($blockedTimeRanges as $range)
                        <li class="flex items-center justify-between gap-3 py-3">
                            <div>
                                <p class="text-sm font-medium">
                                    {{ $range->date->translatedFormat('d F Y') }} · {{ \Illuminate\Support\Carbon::parse($range->start_time)->format('H:i') }} — {{ \Illuminate\Support\Carbon::parse($range->end_time)->format('H:i') }}
                                </p>
                                @if ($range->reason)<p class="text-xs text-ink/50">{{ $range->reason }}</p>@endif
                            </div>
                            <form method="POST" action="{{ route('admin.availability.blocked-ranges.destroy', $range) }}" onsubmit="return confirm('Débloquer cette plage horaire ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-red-600 hover:text-red-800">Supprimer</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-admin.card>
    </div>
</x-layouts.admin>
