<x-layouts.admin title="Apparence du site">
    <x-admin.page-header
        title="Apparence du site"
        subtitle="Bandeau promotionnel en haut du site et visuel principal de la page d'accueil."
    />

    {{-- ============ Bandeau supérieur ============ --}}
    <x-admin.card title="Bandeau supérieur" class="mb-6">
        <form method="POST" action="{{ route('admin.site-content.banner.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-admin.checkbox
                label="Afficher le bandeau au-dessus du menu (visible sur écran large)"
                name="top_banner_enabled"
                :checked="$settings['top_banner_enabled']"
            />
            <x-admin.input
                label="Texte du bandeau"
                name="top_banner_text"
                :value="$settings['top_banner_text']"
                required
                hint="Ex. « Haute coiffure & lissage d'exception — Diagnostic personnalisé offert »"
            />
            <x-admin.input
                label="Sous-texte (facultatif)"
                name="top_banner_subtext"
                :value="$settings['top_banner_subtext']"
                hint="Deuxième ligne, plus discrète. Laisser vide pour n'afficher qu'une seule ligne."
            />

            <button type="submit" class="admin-btn admin-btn-primary">Enregistrer le bandeau</button>
        </form>
    </x-admin.card>

    {{-- ============ Image Hero ============ --}}
    <x-admin.card title="Image de la section Hero">
        <div class="grid gap-6 lg:grid-cols-[240px_1fr]">
            <div>
                <p class="admin-label">Image actuelle</p>
                @if ($heroImageUrl)
                    <img src="{{ $heroImageUrl }}" alt="Image Hero actuelle" class="aspect-[4/5] w-full border border-nude/40 object-cover">
                    <form method="POST" action="{{ route('admin.site-content.hero.destroy') }}" class="mt-3" onsubmit="return confirm('Retirer l’image Hero ? Le visuel par défaut sera réaffiché.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-red-600 hover:text-red-800">Retirer l'image</button>
                    </form>
                @else
                    <div class="flex aspect-[4/5] w-full items-center justify-center border border-dashed border-nude/50 bg-sand/50 p-4 text-center text-xs text-ink/45">
                        Aucune image configurée.<br>Le dégradé par défaut est affiché sur le site.
                    </div>
                @endif
            </div>

            <div>
                <form method="POST" action="{{ route('admin.site-content.hero.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="hero_image" class="admin-label">{{ $heroImageUrl ? 'Remplacer par une nouvelle image' : 'Choisir une image' }}</label>
                        <input type="file" id="hero_image" name="hero_image" accept="image/png,image/jpeg,image/webp" required
                               class="admin-input {{ $errors->has('hero_image') ? 'has-error' : '' }}">
                        @error('hero_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <ul class="space-y-1 text-xs text-ink/45">
                        <li>Formats acceptés : JPG, PNG ou WebP.</li>
                        <li>Taille maximale : 6 Mo.</li>
                        <li>Format conseillé : portrait (ratio 4:5), au moins 900 × 1125 px.</li>
                        <li>L'ancienne image est supprimée automatiquement du stockage.</li>
                    </ul>

                    <button type="submit" class="admin-btn admin-btn-primary">
                        {{ $heroImageUrl ? 'Remplacer l’image' : 'Enregistrer l’image' }}
                    </button>
                </form>
            </div>
        </div>
    </x-admin.card>
</x-layouts.admin>
