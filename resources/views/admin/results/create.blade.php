<x-layouts.admin title="Nouveau résultat">
    <x-admin.page-header title="Ajouter un résultat avant / après" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.results.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <x-admin.input label="Titre" name="title" required />

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="before_image" class="admin-label">Image avant *</label>
                    <input type="file" id="before_image" name="before_image" accept="image/png,image/jpeg,image/webp" required class="admin-input {{ $errors->has('before_image') ? 'has-error' : '' }}">
                    @error('before_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="after_image" class="admin-label">Image après *</label>
                    <input type="file" id="after_image" name="after_image" accept="image/png,image/jpeg,image/webp" required class="admin-input {{ $errors->has('after_image') ? 'has-error' : '' }}">
                    @error('after_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="-mt-3 text-xs text-ink/45">Formats JPG, PNG ou WebP, 4 Mo maximum.</p>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input label="Type de cheveux" name="hair_type" placeholder="Ex. Ondulés et volumineux" />
                <x-admin.input label="Type de lissage" name="lissage_type" placeholder="Ex. Lissage Signature Soyeux" />
            </div>

            <x-admin.textarea label="Description" name="description" rows="3" />
            <x-admin.input label="Ordre d'affichage" name="sort_order" type="number" value="0" />
            <x-admin.checkbox label="Publié sur le site public" name="is_published" :checked="true" />

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="admin-btn admin-btn-primary">Publier</button>
                <a href="{{ route('admin.results.index') }}" class="admin-btn admin-btn-outline">Annuler</a>
            </div>
        </form>
    </x-admin.card>
</x-layouts.admin>
