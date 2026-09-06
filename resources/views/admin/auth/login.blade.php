<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion — AylaLisse Administration</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-cocoa px-4">
    <div class="w-full max-w-sm">
        <div class="mb-8 text-center">
            <x-brand-mark variant="light" class="mx-auto h-9 w-9" />
            <span class="mt-2 block font-serif text-2xl font-semibold text-white">AylaLisse</span>
            <p class="mt-1 text-[0.625rem] font-semibold uppercase tracking-[0.24em] text-taupe">Administration</p>
        </div>

        <div class="admin-card p-8">
            <h1 class="font-serif text-2xl text-cocoa">Bienvenue</h1>
            <p class="mt-1 text-sm text-ink/55">Connectez-vous à votre espace d'administration.</p>

            @if ($errors->any())
                <div class="mt-5 border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-6 space-y-5">
                @csrf

                <x-admin.input label="Adresse e-mail" name="email" type="email" required autofocus autocomplete="username" />
                <x-admin.input label="Mot de passe" name="password" type="password" required autocomplete="current-password" />

                <x-admin.checkbox label="Se souvenir de moi" name="remember" />

                <button type="submit" class="admin-btn admin-btn-primary w-full py-3">Se connecter</button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-white/40">
            <a href="{{ route('home') }}" class="hover:text-white">Retour au site public</a>
        </p>
    </div>
</body>
</html>
