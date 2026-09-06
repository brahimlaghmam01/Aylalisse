@props(['code', 'title', 'text', 'ctaLabel' => 'Retour à l’accueil', 'ctaHref' => null])
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} — AylaLisse</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --cream: #f7f1ec;
            --nude: #cdae9c;
            --taupe: #a98674;
            --cocoa: #25150f;
            --ink: #2d2521;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--cream);
            color: var(--ink);
            font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
            text-align: center;
            padding: 1.5rem;
        }
        .wrap { max-width: 30rem; }
        .code {
            font-family: 'Cormorant Garamond', ui-serif, Georgia, serif;
            font-size: 1.125rem;
            font-weight: 600;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--taupe);
            margin: 0 0 1.25rem;
        }
        h1 {
            font-family: 'Cormorant Garamond', ui-serif, Georgia, serif;
            font-weight: 500;
            font-size: clamp(2rem, 5vw, 2.75rem);
            color: var(--cocoa);
            margin: 0 0 1rem;
            line-height: 1.1;
        }
        p { font-size: 0.9375rem; color: color-mix(in srgb, var(--ink) 70%, transparent); line-height: 1.7; margin: 0 0 2rem; }
        a.cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.9rem 2rem;
            background: var(--cocoa);
            color: #fff;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        a.cta:hover { background: #3a251c; }
    </style>
</head>
<body>
    <div class="wrap">
        <p class="code">AylaLisse — Erreur {{ $code }}</p>
        <h1>{{ $title }}</h1>
        <p>{{ $text }}</p>
        <a class="cta" href="{{ $ctaHref ?? url('/') }}">{{ $ctaLabel }}</a>
    </div>
</body>
</html>
