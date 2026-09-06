<x-layouts.admin title="Tarifs">
    <x-admin.page-header
        title="Grille tarifaire"
        subtitle="Titre, sections et lignes de prix affichés sur la page d'accueil. Rien n'est codé en dur : tout provient d'ici."
    />

    @if ($errors->any())
        <div class="mb-6 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Certaines informations n'ont pas pu être enregistrées :</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ============ Réglages généraux de la grille ============ --}}
    <x-admin.card title="Réglages généraux" class="mb-6">
        <form method="POST" action="{{ route('admin.pricing.settings.update') }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-admin.input label="Titre de la grille" name="pricing_title" :value="$settings['pricing_title']" required />
            <x-admin.textarea label="Texte d'introduction" name="pricing_intro" :value="$settings['pricing_intro']" rows="2" />
            <x-admin.checkbox label="Afficher la grille tarifaire sur le site public" name="pricing_enabled" :checked="$settings['pricing_enabled']" />

            <button type="submit" class="admin-btn admin-btn-primary">Enregistrer les réglages</button>
        </form>
    </x-admin.card>

    {{-- ============ Sections + lignes ============ --}}
    <div class="space-y-6">
        @forelse ($sections as $section)
            <x-admin.card>
                {{-- En-tête de section : édition en place --}}
                <form method="POST" action="{{ route('admin.pricing.sections.update', $section) }}" class="flex flex-wrap items-end gap-3 border-b border-nude/30 pb-5">
                    @csrf
                    @method('PUT')
                    <div class="min-w-[12rem] flex-1">
                        <label class="admin-label">Titre de la section</label>
                        <input type="text" name="title" value="{{ $section->title }}" placeholder="(sans titre — lignes isolées)" class="admin-input">
                    </div>
                    <div class="min-w-[10rem] flex-1">
                        <label class="admin-label">Sous-titre</label>
                        <input type="text" name="subtitle" value="{{ $section->subtitle }}" class="admin-input">
                    </div>
                    <div class="w-20">
                        <label class="admin-label">Ordre</label>
                        <input type="number" name="sort_order" value="{{ $section->sort_order }}" class="admin-input">
                    </div>
                    <label class="flex items-center gap-2 pb-2 text-sm text-ink/80">
                        <input type="checkbox" name="is_active" value="1" @checked($section->is_active) class="h-4 w-4 accent-cocoa"> Active
                    </label>
                    <button type="submit" class="admin-btn admin-btn-outline">Enregistrer</button>
                </form>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
                    <span class="badge {{ $section->is_active ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-ink/5 text-ink/50 border-ink/10' }}">
                        {{ $section->is_active ? 'Section visible' : 'Section masquée' }}
                    </span>
                    <div class="flex items-center gap-4">
                        <form method="POST" action="{{ route('admin.pricing.sections.toggle', $section) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-ink/50 hover:text-cocoa">
                                {{ $section->is_active ? 'Masquer' : 'Afficher' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.pricing.sections.destroy', $section) }}" onsubmit="return confirm('Supprimer cette section et toutes ses lignes ?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-red-600 hover:text-red-800">Supprimer la section</button>
                        </form>
                    </div>
                </div>

                {{-- Lignes de la section : une ligne = un formulaire (flex, pas de table imbriquée) --}}
                <div class="mt-5 space-y-2">
                    <div class="hidden gap-3 px-1 text-[0.625rem] font-semibold uppercase tracking-wide text-ink/40 sm:flex">
                        <span class="flex-1">Intitulé</span>
                        <span class="w-24">Prix (€)</span>
                        <span class="w-32">Mention</span>
                        <span class="w-14">Ordre</span>
                        <span class="w-12 text-center">Active</span>
                        <span class="w-36"></span>
                    </div>

                    @forelse ($section->rows as $row)
                        <div class="flex flex-wrap items-center gap-3 border-b border-ink/5 pb-2 sm:flex-nowrap">
                            <form method="POST" action="{{ route('admin.pricing.rows.update', $row) }}" class="flex flex-1 flex-wrap items-center gap-3 sm:flex-nowrap" id="row-{{ $row->id }}">
                                @csrf @method('PUT')
                                <input type="text" name="label" value="{{ $row->label }}" required class="admin-input flex-1" aria-label="Intitulé">
                                <input type="text" name="price" value="{{ $row->price !== null ? rtrim(rtrim(number_format((float) $row->price, 2, '.', ''), '0'), '.') : '' }}" placeholder="—" class="admin-input w-24" aria-label="Prix">
                                <input type="text" name="note" value="{{ $row->note }}" placeholder="sur devis" class="admin-input w-32" aria-label="Mention">
                                <input type="number" name="sort_order" value="{{ $row->sort_order }}" class="admin-input w-14" aria-label="Ordre">
                                <span class="flex w-12 justify-center"><input type="checkbox" name="is_active" value="1" @checked($row->is_active) class="h-4 w-4 accent-cocoa" aria-label="Active"></span>
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-taupe hover:text-cocoa">Enregistrer</button>
                            </form>
                            <form method="POST" action="{{ route('admin.pricing.rows.destroy', $row) }}" onsubmit="return confirm('Supprimer cette ligne ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-semibold uppercase tracking-wide text-red-600 hover:text-red-800">Suppr.</button>
                            </form>
                        </div>
                    @empty
                        <p class="py-2 text-xs text-ink/40">Aucune ligne dans cette section.</p>
                    @endforelse

                    {{-- Ajout d'une ligne --}}
                    <form method="POST" action="{{ route('admin.pricing.rows.store', $section) }}" class="mt-3 flex flex-wrap items-center gap-3 bg-sand/60 p-3 sm:flex-nowrap">
                        @csrf
                        <input type="text" name="label" value="{{ old('label') }}" placeholder="Nouvel intitulé" class="admin-input flex-1" aria-label="Nouvel intitulé">
                        <input type="text" name="price" value="{{ old('price') }}" placeholder="Prix" class="admin-input w-24" aria-label="Prix">
                        <input type="text" name="note" value="{{ old('note') }}" placeholder="ou mention" class="admin-input w-32" aria-label="Mention">
                        <input type="number" name="sort_order" value="{{ ($section->rows->max('sort_order') ?? 0) + 1 }}" class="admin-input w-14" aria-label="Ordre">
                        <span class="flex w-12 justify-center"><input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 accent-cocoa" aria-label="Active"></span>
                        <button type="submit" class="admin-btn admin-btn-outline">+ Ajouter</button>
                    </form>
                </div>
            </x-admin.card>
        @empty
            <p class="text-sm text-ink/50">Aucune section pour l'instant. Ajoutez-en une ci-dessous.</p>
        @endforelse
    </div>

    {{-- ============ Ajouter une section ============ --}}
    <x-admin.card title="Ajouter une section" class="mt-6">
        <form method="POST" action="{{ route('admin.pricing.sections.store') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="min-w-[12rem] flex-1">
                <label class="admin-label">Titre <span class="font-normal normal-case text-ink/40">(laisser vide pour des lignes isolées)</span></label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="ex. Lissage indien / brésilien" class="admin-input">
            </div>
            <div class="w-24">
                <label class="admin-label">Ordre</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', ($sections->max('sort_order') ?? 0) + 1) }}" class="admin-input">
            </div>
            <button type="submit" class="admin-btn admin-btn-primary">+ Créer la section</button>
        </form>
    </x-admin.card>
</x-layouts.admin>
