<x-layouts.admin title="Nouvelle prestation">
    <x-admin.page-header title="Ajouter une prestation" subtitle="Toute nouvelle prestation doit rester dans le périmètre du lissage." />

    <x-admin.card>
        <form method="POST" action="{{ route('admin.services.store') }}" class="space-y-5">
            @csrf

            <x-admin.input label="Nom" name="name" required />
            <x-admin.input label="Description courte" name="short_description" required hint="Affichée sur les cartes du site public et de la réservation." />
            <x-admin.textarea label="Description" name="description" rows="4" />

            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input label="Prix (€)" name="price" type="number" value="0" hint="Laisser à 0 pour afficher « Sur devis »." required />
                <x-admin.input label="Acompte (€)" name="deposit_amount" type="number" value="0" required />
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
