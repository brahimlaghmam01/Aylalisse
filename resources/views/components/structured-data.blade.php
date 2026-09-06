@php
    // Uniquement des données réellement configurées — jamais de valeur inventée.
    $phone = \App\Models\Setting::getCached('brand_phone');
    $email = \App\Models\Setting::getCached('brand_email');
    $addressLine = \App\Models\Setting::getCached('brand_address_line');
    $addressZip = \App\Models\Setting::getCached('brand_address_zip');
    $addressCity = \App\Models\Setting::getCached('brand_address_city');
    $instagram = \App\Models\Setting::getCached('brand_instagram');

    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'BeautySalon',
        'name' => config('aylalisse.brand'),
        'url' => url('/'),
    ];

    if ($phone) {
        $data['telephone'] = $phone;
    }

    if ($email) {
        $data['email'] = $email;
    }

    if ($addressLine || $addressCity) {
        $data['address'] = array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $addressLine,
            'postalCode' => $addressZip,
            'addressLocality' => $addressCity,
            'addressCountry' => 'FR',
        ]);
    }

    $sameAs = array_values(array_filter([$instagram]));
    if ($sameAs !== []) {
        $data['sameAs'] = $sameAs;
    }
@endphp
<script type="application/ld+json">{!! json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
