<x-layouts.admin title="Nouveau témoignage">
    <x-admin.page-header title="Ajouter un témoignage" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.testimonials.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <x-admin.input label="Nom de la cliente" name="client_name" required />
            <x-admin.textarea label="Témoignage" name="content" rows="4" required />
            <x-admin.select label="Note" name="rating" :options="[5 => '5 étoiles', 4 => '4 étoiles', 3 => '3 étoiles', 2 => '2 étoiles', 1 => '1 étoile']" value="5" required />

            <div>
                <label for="image" class="admin-label">Photo (facultatif)</label>
                <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp" class="admin-input {{ $errors->has('image') ? 'has-error' : '' }}">
                @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <x-admin.input label="Ordre d'affichage" name="sort_order" type="number" value="0" />
            <x-admin.checkbox label="Publié sur le site public" name="is_published" :checked="true" />

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="admin-btn admin-btn-primary">Publier</button>
                <a href="{{ route('admin.testimonials.index') }}" class="admin-btn admin-btn-outline">Annuler</a>
            </div>
        </form>
    </x-admin.card>
</x-layouts.admin>
