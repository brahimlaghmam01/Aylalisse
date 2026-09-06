<x-layouts.admin :title="'Modifier — '.$testimonial->client_name">
    <x-admin.page-header title="Modifier le témoignage" :subtitle="$testimonial->client_name" />

    <x-admin.card>
        @if ($testimonial->image)
            <div class="mb-6">
                <p class="admin-label">Photo actuelle</p>
                <img src="{{ Storage::url($testimonial->image) }}" alt="{{ $testimonial->client_name }}" class="h-24 w-24 border border-nude/40 object-cover">
            </div>
        @endif

        <form method="POST" action="{{ route('admin.testimonials.update', $testimonial) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <x-admin.input label="Nom de la cliente" name="client_name" :value="$testimonial->client_name" required />
            <x-admin.textarea label="Témoignage" name="content" :value="$testimonial->content" rows="4" required />
            <x-admin.select label="Note" name="rating" :options="[5 => '5 étoiles', 4 => '4 étoiles', 3 => '3 étoiles', 2 => '2 étoiles', 1 => '1 étoile']" :value="$testimonial->rating" required />

            <div>
                <label for="image" class="admin-label">Remplacer la photo</label>
                <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp" class="admin-input {{ $errors->has('image') ? 'has-error' : '' }}">
                @error('image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <x-admin.input label="Ordre d'affichage" name="sort_order" type="number" :value="$testimonial->sort_order" />
            <x-admin.checkbox label="Publié sur le site public" name="is_published" :checked="$testimonial->is_published" />

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="admin-btn admin-btn-primary">Enregistrer</button>
                <a href="{{ route('admin.testimonials.index') }}" class="admin-btn admin-btn-outline">Annuler</a>
            </div>
        </form>
    </x-admin.card>
</x-layouts.admin>
