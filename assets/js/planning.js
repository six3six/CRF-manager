import '../css/planning.scss';
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


require('bootstrap');

let dataSource = "/planning/source";
let dataInsert = "/planning/entry/";
$(function () {
    let add_event_modal = $("#addEventModal");

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
        events: dataSource,
        dateClick: function (info) {
            window.location.href = dataInsert + "new/" + info.date.toISOString() + "/" + info.date.toISOString();
        },
        select: function (info) {
            window.location.href = dataInsert + "new/" + info.start.toISOString() + "/" + info.end.toISOString();
        },
        eventClick: function (info) {
            console.log(info.event.type);
            if (info.event.type === "availability") window.location.href = info.event.url;
        }
    });
    calendar.render();
});

let showEventModal = function () {
    let eventModal = $("#eventModal");
    eventModal.modal("show");
}




