import '../css/planning.scss';
require("core-js");

import {Calendar} from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

import frLocale from '@fullcalendar/core/locales/fr';

import '@fullcalendar/core/main.css';
import '@fullcalendar/daygrid/main.css';
import '@fullcalendar/timegrid/main.css';

import $ from 'jquery';

let dataSource = "/planning/source/";
let dataInsert = "/planning/insert";
$(function () {
    let add_event_modal = $("#addEventModal");

    let calendarEl = $("#calendar")[0];
    let calendar = new Calendar(calendarEl, {
        locale: frLocale,
        plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
        defaultView: 'dayGridMonth',
        selectable: true,
        timezone: 'CET',
        header: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        eventSources: [
            {
                url: dataSource + "availabilities",
                color: 'blue',
            },
            {
                url: dataSource + "events",
                color: 'red',
            },
            {
                url: dataSource + "tests",
                color: 'yellow',
            }
        ],
        dateClick: function (info) {
            window.location.href = "/planning/insert?start=" + info.dateStr + "&stop=" + info.dateStr;
        },
        select: function (info) {
            window.location.href = "/planning/insert?start=" + info.startStr + "&stop=" + info.endStr;
        }
    });
    calendar.render();
});




