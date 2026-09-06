@php
    // Repli sur config/aylalisse.php tant qu'aucune valeur n'a été
    // enregistrée depuis /admin/parametres (Setting::get gère déjà ça).
    $phone = \App\Models\Setting::getCached('brand_phone', config('aylalisse.phone'));
    $whatsapp = \App\Models\Setting::getCached('brand_whatsapp', config('aylalisse.whatsapp'));
    $email = \App\Models\Setting::getCached('brand_email', config('aylalisse.email'));
    $instagram = \App\Models\Setting::getCached('brand_instagram', config('aylalisse.instagram'));
    $addressLine = \App\Models\Setting::getCached('brand_address_line', config('aylalisse.address.line'));
    $addressZip = \App\Models\Setting::getCached('brand_address_zip', config('aylalisse.address.zip'));
    $addressCity = \App\Models\Setting::getCached('brand_address_city', config('aylalisse.address.city'));
@endphp

<footer id="contact" class="bg-cocoa text-cream/80">
    <div class="container-editorial section-y">
        <div class="grid gap-12 lg:grid-cols-4">
            {{-- Marque --}}
            <div class="lg:col-span-1">
                <span class="flex items-center gap-3">
                    <x-brand-mark variant="light" class="h-8 w-8 shrink-0" />
                    <span class="font-serif text-2xl font-semibold text-white">AylaLisse</span>
                </span>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-cream/60">
                    Maison dédiée au lissage des cheveux. Diagnostic capillaire personnalisé et
                    transformations soyeuses, pensées pour révéler la beauté naturelle de vos cheveux.
                </p>
            </div>

            {{-- Navigation --}}
            <div>
                <p class="eyebrow text-taupe">Navigation</p>
                <ul class="mt-5 space-y-3 text-sm">
                    <li><a href="{{ url('/') }}#le-lissage" class="transition-colors hover:text-white">Le lissage</a></li>
                    <li><a href="{{ url('/') }}#resultats" class="transition-colors hover:text-white">Résultats</a></li>
                    <li><a href="{{ url('/') }}#tarifs" class="transition-colors hover:text-white">Tarifs</a></li>
                    <li><a href="{{ url('/') }}#notre-methode" class="transition-colors hover:text-white">Notre méthode</a></li>
                    <li><a href="{{ url('/') }}#temoignages" class="transition-colors hover:text-white">Témoignages</a></li>
                    <li><a href="{{ url('/') }}#faq" class="transition-colors hover:text-white">FAQ</a></li>
                    <li><a href="{{ route('booking') }}" class="transition-colors hover:text-white">Prendre rendez-vous</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <p class="eyebrow text-taupe">Contact</p>
                <ul class="mt-5 space-y-3 text-sm">
                    <li><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="transition-colors hover:text-white">{{ $phone }}</a></li>
                    <li><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" class="transition-colors hover:text-white">WhatsApp · {{ $whatsapp }}</a></li>
                    <li><a href="mailto:{{ $email }}" class="transition-colors hover:text-white">{{ $email }}</a></li>
                    <li><a href="{{ $instagram }}" class="transition-colors hover:text-white">Instagram</a></li>
                </ul>
            </div>

            {{-- Adresse & horaires --}}
            <div>
                <p class="eyebrow text-taupe">Salon &amp; horaires</p>
                <address class="mt-5 not-italic text-sm leading-relaxed text-cream/70">
                    {{ $addressLine }}<br>
                    {{ $addressZip }} {{ $addressCity }}
                </address>
                <ul class="mt-4 space-y-1.5 text-sm text-cream/70">
                    @foreach (config('aylalisse.hours') as $h)
                        <li class="flex justify-between gap-4"><span>{{ $h['jour'] }}</span><span class="text-cream/50">{{ $h['creneau'] }}</span></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="mt-16 flex flex-col gap-4 border-t border-white/10 pt-8 text-xs text-cream/50 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ date('Y') }} AylaLisse. Tous droits réservés.</p>
            <nav class="flex flex-wrap gap-x-6 gap-y-2">
                <a href="{{ route('legal.mentions') }}" class="transition-colors hover:text-white">Mentions légales</a>
                <a href="{{ route('legal.privacy') }}" class="transition-colors hover:text-white">Politique de confidentialité</a>
                <a href="{{ route('legal.terms') }}" class="transition-colors hover:text-white">Conditions de réservation</a>
            </nav>
        </div>
    </div>
</footer>
