<x-layouts.admin title="Témoignages">
    <x-admin.page-header title="Témoignages" subtitle="Les avis mis en avant sur le site public.">
        <x-slot:actions>
            <a href="{{ route('admin.testimonials.create') }}" class="admin-btn admin-btn-primary">+ Ajouter un témoignage</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card overflow-x-auto">
        <table class="admin-table w-full">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Témoignage</th>
                    <th>Note</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($testimonials as $testimonial)
                    <tr>
                        <td class="font-medium">{{ $testimonial->client_name }}</td>
                        <td class="max-w-sm truncate">{{ $testimonial->content }}</td>
                        <td>{{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}</td>
                        <td>
                            <span class="badge {{ $testimonial->is_published ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-ink/5 text-ink/50 border-ink/10' }}">
                                {{ $testimonial->is_published ? 'Publié' : 'Masqué' }}
                            </span>
                        </td>
                        <td class="text-right whitespace-nowrap">
                            <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">Modifier</a>
                            <form method="POST" action="{{ route('admin.testimonials.toggle', $testimonial) }}" class="ml-3 inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-ink/50 hover:text-cocoa">
                                    {{ $testimonial->is_published ? 'Masquer' : 'Publier' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.testimonials.destroy', $testimonial) }}" class="ml-3 inline" onsubmit="return confirm('Supprimer définitivement ce témoignage ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-red-600 hover:text-red-800">Suppr.</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-sm text-ink/50">Aucun témoignage.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $testimonials->links() }}</div>
</x-layouts.admin>
