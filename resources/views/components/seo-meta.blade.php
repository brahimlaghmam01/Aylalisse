@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'noindex' => false,
])
@php
    $siteName = config('aylalisse.brand');
    $resolvedTitle = $title ?: $siteName.' — '.config('aylalisse.tagline');
    $resolvedDescription = $description ?: 'AylaLisse, spécialiste du lissage des cheveux. Diagnostic capillaire personnalisé et lissage d’exception pour une chevelure lisse, soyeuse et élégante.';
    $canonical = url()->current();
@endphp
<title>{{ $resolvedTitle }}</title>
<meta name="description" content="{{ $resolvedDescription }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- Favicon --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}">

@if ($noindex)
    <meta name="robots" content="noindex, nofollow">
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $resolvedTitle }}">
<meta property="og:description" content="{{ $resolvedDescription }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="fr_FR">
@if ($image)
    <meta property="og:image" content="{{ $image }}">
@endif

{{-- Twitter Card --}}
<meta name="twitter:card" content="{{ $image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $resolvedTitle }}">
<meta name="twitter:description" content="{{ $resolvedDescription }}">
@if ($image)
    <meta name="twitter:image" content="{{ $image }}">
@endif
