<x-layouts.admin :title="'Modifier — '.$result->title">
    <x-admin.page-header title="Modifier le résultat" :subtitle="$result->title" />

    <x-admin.card>
        <div class="mb-6 grid grid-cols-2 gap-4">
            <div>
                <p class="admin-label">Image avant actuelle</p>
                <img src="{{ Storage::url($result->before_image) }}" alt="Avant" class="h-32 w-full border border-nude/40 object-cover">
            </div>
            <div>
                <p class="admin-label">Image après actuelle</p>
                <img src="{{ Storage::url($result->after_image) }}" alt="Après" class="h-32 w-full border border-nude/40 object-cover">
            </div>
        </div>

        <form method="POST" action="{{ route('admin.results.update', $result) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <x-admin.input label="Titre" name="title" :value="$result->title" required />

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="before_image" class="admin-label">Remplacer l'image avant</label>
                    <input type="file" id="before_image" name="before_image" accept="image/png,image/jpeg,image/webp" class="admin-input {{ $errors->has('before_image') ? 'has-error' : '' }}">
                    @error('before_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="after_image" class="admin-label">Remplacer l'image après</label>
                    <input type="file" id="after_image" name="after_image" accept="image/png,image/jpeg,image/webp" class="admin-input {{ $errors->has('after_image') ? 'has-error' : '' }}">
                    @error('after_image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input label="Type de cheveux" name="hair_type" :value="$result->hair_type" />
                <x-admin.input label="Type de lissage" name="lissage_type" :value="$result->lissage_type" />
            </div>

            <x-admin.textarea label="Description" name="description" :value="$result->description" rows="3" />
            <x-admin.input label="Ordre d'affichage" name="sort_order" type="number" :value="$result->sort_order" />
            <x-admin.checkbox label="Publié sur le site public" name="is_published" :checked="$result->is_published" />

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="admin-btn admin-btn-primary">Enregistrer</button>
                <a href="{{ route('admin.results.index') }}" class="admin-btn admin-btn-outline">Annuler</a>
            </div>
        </form>
    </x-admin.card>
</x-layouts.admin>
