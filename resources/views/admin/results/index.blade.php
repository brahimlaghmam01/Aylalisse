<x-layouts.admin title="Résultats avant / après">
    <x-admin.page-header title="Résultats avant / après" subtitle="Les transformations mises en avant sur le site public.">
        <x-slot:actions>
            <a href="{{ route('admin.results.create') }}" class="admin-btn admin-btn-primary">+ Ajouter un résultat</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($results as $result)
            <div class="admin-card overflow-hidden">
                <div class="grid grid-cols-2 bg-sand">
                    <img src="{{ $result->before_image_url }}" alt="Avant — {{ $result->title }}" class="h-32 w-full object-cover" loading="lazy">
                    <img src="{{ $result->after_image_url }}" alt="Après — {{ $result->title }}" class="h-32 w-full object-cover" loading="lazy">
                </div>
                <div class="p-4">
                    <p class="font-serif text-lg text-cocoa">{{ $result->title }}</p>
                    <p class="mt-1 text-xs text-ink/50">{{ $result->hair_type }} @if($result->hair_type && $result->lissage_type) · @endif {{ $result->lissage_type }}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="badge {{ $result->is_published ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-ink/5 text-ink/50 border-ink/10' }}">
                            {{ $result->is_published ? 'Publié' : 'Masqué' }}
                        </span>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.results.edit', $result) }}" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">Modifier</a>
                            <form method="POST" action="{{ route('admin.results.toggle', $result) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-ink/50 hover:text-cocoa">
                                    {{ $result->is_published ? 'Masquer' : 'Publier' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.results.destroy', $result) }}" onsubmit="return confirm('Supprimer définitivement ce résultat ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-red-600 hover:text-red-800">Suppr.</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-ink/50">Aucun résultat pour l'instant.</p>
        @endforelse
    </div>

    <div class="mt-6">{{ $results->links() }}</div>
</x-layouts.admin>
