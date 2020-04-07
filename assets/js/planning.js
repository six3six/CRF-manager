import '../css/planning.scss';
import '@fullcalendar/core/main.css';
import '@fullcalendar/daygrid/main.css';

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';

import $ from 'jquery';
$(function () {
    let calendarEl = $("#calendar")[0];
    let calendar = new Calendar(calendarEl, {
        plugins: [ dayGridPlugin ],
        defaultView: 'dayGridMonth',
        selectable: true
    });
    calendar.render();
    console.log(calendar);
});



