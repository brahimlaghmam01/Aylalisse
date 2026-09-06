<x-layouts.public>
    @section('title', $titre . ' — AylaLisse')

    <section class="section-y">
        <div class="container-editorial max-w-2xl">
            <p class="eyebrow">AylaLisse</p>
            <h1 class="display-2 mt-4">{{ $titre }}</h1>
            <div class="hairline mt-8 pt-8 text-ink/70">
                <p>{{ $contenu }}</p>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline mt-10">Retour à l’accueil</a>
        </div>
    </section>
</x-layouts.public>
