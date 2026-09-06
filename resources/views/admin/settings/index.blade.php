<x-layouts.admin title="Paramètres">
    <x-admin.page-header title="Paramètres" subtitle="Les réglages de réservation ont un effet réel et immédiat sur le moteur de disponibilité." />

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.card title="Informations générales">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input label="Téléphone" name="brand_phone" :value="$settings['brand_phone']" required />
                <x-admin.input label="WhatsApp" name="brand_whatsapp" :value="$settings['brand_whatsapp']" required />
                <x-admin.input label="E-mail" name="brand_email" type="email" :value="$settings['brand_email']" required />
                <x-admin.input label="Instagram" name="brand_instagram" type="url" :value="$settings['brand_instagram']" />
                <x-admin.input label="Adresse" name="brand_address_line" :value="$settings['brand_address_line']" required />
                <x-admin.input label="Code postal" name="brand_address_zip" :value="$settings['brand_address_zip']" required />
                <x-admin.input label="Ville" name="brand_address_city" :value="$settings['brand_address_city']" required />
            </div>
        </x-admin.card>

        <x-admin.card title="Réservation">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.input
                    label="Intervalle des créneaux (minutes)" name="booking_interval" type="number"
                    :value="$settings['booking_interval']" required
                    hint="Ex. 30 → créneaux proposés toutes les 30 minutes."
                />
                <x-admin.input
                    label="Délai minimum de réservation (heures)" name="minimum_booking_notice_hours" type="number"
                    :value="$settings['minimum_booking_notice_hours']" required
                    hint="Aucun créneau ne sera proposé avant ce délai."
                />
                <x-admin.input
                    label="Horizon maximum de réservation (jours)" name="maximum_booking_days" type="number"
                    :value="$settings['maximum_booking_days']" required
                    hint="Les clientes ne peuvent pas réserver au-delà."
                />
                <x-admin.input
                    label="Tampon par défaut (minutes)" name="default_buffer_minutes" type="number"
                    :value="$settings['default_buffer_minutes']" required
                    hint="Utilisé à titre indicatif ; chaque prestation garde son propre tampon."
                />
            </div>
        </x-admin.card>

        <button type="submit" class="admin-btn admin-btn-primary">Enregistrer les paramètres</button>
    </form>
</x-layouts.admin>
