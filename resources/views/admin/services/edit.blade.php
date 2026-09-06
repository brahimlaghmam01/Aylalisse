<x-layouts.admin :title="'Modifier — '.$service->name">
    <x-admin.page-header title="Modifier la prestation" :subtitle="$service->name" />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.services.update', $service) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <x-admin.input label="Nom" name="name" :value="$service->name" required />
            <x-admin.input label="Description courte" name="short_description" :value="$service->short_description" required />
            <x-admin.textarea label="Description" name="description" :value="$service->description" rows="4" />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input label="Prix (€)" name="price" type="number" :value="$service->price" hint="Laisser à 0 pour afficher « Sur devis »." required />
                <x-admin.input label="Acompte (€)" name="deposit_amount" type="number" :value="$service->deposit_amount" required />
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
