import '../css/planning.scss';

require("core-js");

import {Calendar} from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import listPlugin from '@fullcalendar/list';

import frLocale from '@fullcalendar/core/locales/fr';

import '@fullcalendar/core/main.css';
import '@fullcalendar/daygrid/main.css';
import '@fullcalendar/timegrid/main.css';
import '@fullcalendar/list/main.css';

import $ from 'jquery';

let dataSource = "/planning/source/";
$(function () {
    let add_event_modal = $("#addEventModal");
    let user_id = $("#user_id").val();

    let calendarEl = $("#calendar")[0];
    let calendar = new Calendar(calendarEl, {
        locale: frLocale,
        plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin, listPlugin],
        defaultView: 'dayGridMonth',
        selectable: true,
        timezone: 'CET',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        eventSources: [
            {
                url: dataSource + "availabilities",
                color: 'blue',
            },
        ],
        dateClick: function (info) {
        },
        select: function (info) {
        },
        eventClick: function (info) {
            if (info.event.type === "availability") window.location.href = info.event.url;
        }
    });
    calendar.render();
});




