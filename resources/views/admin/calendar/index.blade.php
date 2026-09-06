<x-layouts.admin title="Calendrier">
    <x-admin.page-header title="Calendrier" subtitle="Vue d'ensemble des rendez-vous — mois, semaine ou jour." />

    <div class="admin-card p-4">
        <div class="mb-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink/50">
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5" style="background:#a98674"></span>En attente</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5" style="background:#3a251c"></span>Confirmé</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5" style="background:#cdae9c"></span>Terminé</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5" style="background:#b45454"></span>Annulé</span>
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5" style="background:#9a938d"></span>Absente</span>
        </div>

        {{-- FullCalendar s'initialise une seule fois sur ce conteneur (resources/js/admin-calendar.js) --}}
        <div id="admin-calendar"></div>
    </div>
</x-layouts.admin>
