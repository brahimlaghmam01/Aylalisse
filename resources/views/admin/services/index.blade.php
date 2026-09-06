<x-layouts.admin title="Prestations">
    <x-admin.page-header title="Prestations de lissage" subtitle="AylaLisse est exclusivement spécialisée dans le lissage des cheveux.">
        <x-slot:actions>
            <a href="{{ route('admin.services.create') }}" class="admin-btn admin-btn-primary">+ Ajouter une prestation</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-x-auto">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prix</th>
                    <th>Acompte</th>
                    <th>Durée</th>
                    <th>Tampon</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                    <tr>
                        <td class="font-medium">{{ $service->name }}</td>
                        <td>
                            @if ($service->is_on_quote)
                                Sur devis
                            @elseif ($service->hasLengthPricing())
                                <span class="text-xs text-ink/60">
                                    @foreach ($service->lengthPrices() as $length => $amount)
                                        {{ ['courts' => 'C', 'mi-longs' => 'M', 'longs' => 'L'][$length] ?? $length }}&nbsp;{{ number_format($amount, 0, ',', ' ') }}&nbsp;€@if(! $loop->last) · @endif
                                    @endforeach
                                </span>
                            @else
                                {{ number_format((float) $service->price, 2, ',', ' ') }} €
                            @endif
                        </td>
                        <td>{{ number_format((float) $service->deposit_amount, 2, ',', ' ') }} €</td>
                        <td>{{ $service->duration_minutes }} min</td>
                        <td>{{ $service->buffer_minutes }} min</td>
                        <td>
                            <span class="badge {{ $service->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-ink/5 text-ink/50 border-ink/10' }}">
                                {{ $service->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.services.edit', $service) }}" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">Modifier</a>
                            <form method="POST" action="{{ route('admin.services.toggle', $service) }}" class="ml-3 inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-ink/50 hover:text-cocoa">
                                    {{ $service->is_active ? 'Désactiver' : 'Réactiver' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-sm text-ink/50">Aucune prestation.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
