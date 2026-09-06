@props(['hideFloatingCta' => false, 'structuredData' => false, 'whatsappMessage' => null])
<!DOCTYPE html>
<html lang="fr" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <x-seo-meta
        :title="trim($__env->yieldContent('title')) ?: null"
        :description="trim($__env->yieldContent('meta_description')) ?: null"
        :noindex="trim($__env->yieldContent('noindex')) === '1'"
    />
    @if ($structuredData)
        <x-structured-data />
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream text-ink {{ $hideFloatingCta ? '' : 'pb-20 lg:pb-0' }}">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-4 focus:bg-cocoa focus:px-4 focus:py-2 focus:text-white">Aller au contenu</a>

    <x-site-header />

    <main id="contenu">
        {{ $slot }}
    </main>

    <x-site-footer />

    <x-whatsapp-button :message="$whatsappMessage" :class="$hideFloatingCta ? 'bottom-6' : 'bottom-24 lg:bottom-6'" />

    @unless ($hideFloatingCta)
        {{-- CTA flottant mobile --}}
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-nude/40 bg-cream/95 p-3 backdrop-blur lg:hidden">
            <a href="{{ route('booking') }}" class="btn btn-primary w-full">Prendre rendez-vous</a>
        </div>
    @endunless
</body>
</html>
