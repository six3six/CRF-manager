import '../css/planning.scss';
import '@fullcalendar/core/main.css';
import '@fullcalendar/daygrid/main.css';

import {Calendar} from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

import frLocale from '@fullcalendar/core/locales/fr';

import $ from 'jquery';

let dataSource = "/planning/source/";
$(function () {
    let calendarEl = $("#calendar")[0];
    let calendar = new Calendar(calendarEl, {
        locale: frLocale,
        plugins: [dayGridPlugin, interactionPlugin],
        defaultView: 'dayGridMonth',
        selectable: true,
        eventSources: [
            {
                url: dataSource + "availabilities",
                color: 'blue',   // a non-ajax option
            },
            {
                url: dataSource + "events",
                color: 'red',   // a non-ajax option
            },
            {
                url: dataSource + "tests",
                color: 'yellow',   // a non-ajax option
            }
        ],
        dateClick: function (info) {
            alert('clicked ' + info.dateStr);
        },
        select: function (info) {
            alert('selected ' + info.startStr + ' to ' + info.endStr);
        }
    });
    calendar.render();
});



