<x-layouts.admin :title="'Modifier — '.$service->name">
    <x-admin.page-header title="Modifier la prestation" :subtitle="$service->name" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <x-admin.input label="Nom" name="name" :value="$service->name" required />
            <x-admin.input label="Description courte" name="short_description" :value="$service->short_description" required />
            <x-admin.textarea label="Description" name="description" :value="$service->description" rows="4" />

            <div class="grid gap-6 sm:grid-cols-[160px_1fr]">
                <div>
                    <p class="admin-label">Photo actuelle</p>
                    @if ($service->image_url)
                        <img src="{{ $service->image_url }}" alt="{{ $service->name }}" class="aspect-[4/3] w-full border border-nude/40 object-cover">
                    @else
                        <div class="flex aspect-[4/3] w-full items-center justify-center border border-dashed border-nude/50 bg-sand/40 text-center text-xs text-ink/45">Aucune photo</div>
                    @endif
                </div>
                <div>
                    <label for="image" class="admin-label">{{ $service->image_url ? 'Remplacer la photo' : 'Ajouter une photo' }}</label>
                    <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp"
                           class="admin-input {{ $errors->has('image') ? 'has-error' : '' }}">
                    <p class="mt-1 text-xs text-ink/45">JPG, PNG ou WebP, 4 Mo max. L'ancienne photo est supprimée automatiquement.</p>
                    @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input label="Prix forfaitaire (€)" name="price" type="number" step="0.01" :value="$service->price" hint="Utilisé si aucun tarif par longueur n'est renseigné. Laisser à 0 pour « Sur devis »." required />
                <x-admin.input label="Acompte (€)" name="deposit_amount" type="number" step="0.01" :value="$service->deposit_amount" required />
            </div>

            <fieldset class="border border-nude/40 p-4">
                <legend class="admin-label px-1">Tarifs par longueur de cheveux (facultatif)</legend>
                <p class="mb-3 text-xs text-ink/45">Dès qu'un de ces champs est renseigné, le prix facturé à la réservation dépend de la longueur choisie. Vider un champ le désactive.</p>
                <div class="grid gap-5 sm:grid-cols-3">
                    <x-admin.input label="Cheveux courts (€)" name="price_courts" type="number" step="0.01" :value="$service->price_courts" placeholder="—" />
                    <x-admin.input label="Cheveux mi-longs (€)" name="price_mi_longs" type="number" step="0.01" :value="$service->price_mi_longs" placeholder="—" />
                    <x-admin.input label="Cheveux longs (€)" name="price_longs" type="number" step="0.01" :value="$service->price_longs" placeholder="—" />
                </div>
            </fieldset>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input label="Durée (minutes)" name="duration_minutes" type="number" :value="$service->duration_minutes" required />
                <x-admin.input label="Tampon (minutes)" name="buffer_minutes" type="number" :value="$service->buffer_minutes" />
                <x-admin.input label="Ordre d'affichage" name="sort_order" type="number" :value="$service->sort_order" />
            </div>

            <x-admin.checkbox label="Prestation active (visible sur le booking public)" name="is_active" :checked="$service->is_active" />

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="admin-btn admin-btn-primary">Enregistrer</button>
                <a href="{{ route('admin.services.index') }}" class="admin-btn admin-btn-outline">Annuler</a>
            </div>
        </form>
    </x-admin.card>
</x-layouts.admin>
