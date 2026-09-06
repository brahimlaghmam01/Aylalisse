<x-layouts.admin title="Nouvelle prestation">
    <x-admin.page-header title="Ajouter une prestation" subtitle="Toute nouvelle prestation doit rester dans le périmètre du lissage." />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <x-admin.input label="Nom" name="name" required />
            <x-admin.input label="Description courte" name="short_description" required hint="Affichée sur les cartes du site public et de la réservation." />
            <x-admin.textarea label="Description" name="description" rows="4" />

            <div>
                <label for="image" class="admin-label">Photo de la prestation (facultatif)</label>
                <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp"
                       class="admin-input {{ $errors->has('image') ? 'has-error' : '' }}">
                <p class="mt-1 text-xs text-ink/45">JPG, PNG ou WebP, 4 Mo max. Illustre la carte de la prestation sur le site public.</p>
                @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input label="Prix forfaitaire (€)" name="price" type="number" step="0.01" value="0" hint="Utilisé si aucun tarif par longueur n'est renseigné. Laisser à 0 pour afficher « Sur devis »." required />
                <x-admin.input label="Acompte (€)" name="deposit_amount" type="number" step="0.01" value="0" required />
            </div>

            <fieldset class="border border-nude/40 p-4">
                <legend class="admin-label px-1">Tarifs par longueur de cheveux (facultatif)</legend>
                <p class="mb-3 text-xs text-ink/45">Dès qu'un de ces champs est renseigné, le prix facturé à la réservation dépend de la longueur choisie par la cliente (le prix forfaitaire ci-dessus sert de repli pour les longueurs non renseignées).</p>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-admin.input label="Cheveux courts (€)" name="price_courts" type="number" step="0.01" placeholder="—" />
                    <x-admin.input label="Cheveux mi-longs (€)" name="price_mi_longs" type="number" step="0.01" placeholder="—" />
                    <x-admin.input label="Cheveux longs (€)" name="price_longs" type="number" step="0.01" placeholder="—" />
                </div>
            </fieldset>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input label="Durée (minutes)" name="duration_minutes" type="number" value="180" required />
                <x-admin.input label="Tampon (minutes)" name="buffer_minutes" type="number" value="30" />
                <x-admin.input label="Ordre d'affichage" name="sort_order" type="number" value="0" />
            </div>

            <x-admin.checkbox label="Prestation active (visible sur le booking public)" name="is_active" :checked="true" />

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="admin-btn admin-btn-primary">Enregistrer</button>
                <a href="{{ route('admin.services.index') }}" class="admin-btn admin-btn-outline">Annuler</a>
            </div>
        </form>
    </x-admin.card>
</x-layouts.admin>
