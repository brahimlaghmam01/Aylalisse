import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import frLocale from '@fullcalendar/core/locales/fr';

/**
 * Calendrier admin (/admin/calendrier). Instancié une seule fois, au chargement
 * de la page, lorsque son conteneur existe réellement dans le DOM — jamais
 * détruit/recréé par un toggle Alpine (voir la note Phase 3 sur les
 * transitions Alpine qui peuvent entrer en conflit avec un contenu qui se
 * re-rend pendant l'animation : FullCalendar gère son propre cycle de vie,
 * on ne le mêle à aucun x-show/x-transition/x-collapse).
 */
document.addEventListener('DOMContentLoaded', () => {
    const el = document.getElementById('admin-calendar');
    if (! el) {
        return;
    }

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        locale: frLocale,
        firstDay: 1,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay',
        },
        events(info, successCallback, failureCallback) {
            window.axios
                .get('/admin/calendrier/evenements', {
                    params: { start: info.startStr, end: info.endStr },
                })
                .then((response) => successCallback(response.data))
                .catch((error) => failureCallback(error));
        },
        eventClick(info) {
            info.jsEvent.preventDefault();
            const url = info.event.extendedProps.detailUrl || info.event.url;
            if (url) {
                window.location.href = url;
            }
        },
    });

    calendar.render();
});
