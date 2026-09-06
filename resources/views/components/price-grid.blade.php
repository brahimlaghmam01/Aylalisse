@props(['sections'])

{{--
    Grille tarifaire publique. Les sections et lignes proviennent de la base
    (tables price_sections / price_rows), administrées depuis /admin/tarifs.
    Aucun prix n'est codé ici.
--}}
<div class="mx-auto mt-14 max-w-2xl space-y-12">
    @foreach ($sections as $section)
        <div>
            @if ($section->title)
                <h3 class="display-3 text-2xl text-cocoa">{{ $section->title }}</h3>
            @endif
            @if ($section->subtitle)
                <p class="mt-2 text-sm text-ink/55">{{ $section->subtitle }}</p>
            @endif

            <dl class="{{ $section->title || $section->subtitle ? 'mt-6' : '' }} divide-y divide-ink/10 border-y border-ink/10">
                @foreach ($section->activeRows as $row)
                    <div class="flex items-baseline justify-between gap-4 py-3.5">
                        <dt class="text-sm uppercase tracking-[0.08em] text-ink/70">{{ $row->label }}</dt>
                        <span class="mx-3 hidden flex-1 border-b border-dotted border-ink/25 sm:block"></span>
                        <dd class="font-serif text-lg text-cocoa whitespace-nowrap">{{ $row->formatted_price }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    @endforeach
</div>
