import '../css/planning.scss';
require("core-js");

import $ from 'jquery';
import "jquery-datetimepicker/build/jquery.datetimepicker.min.css";
require("jquery-datetimepicker/build/jquery.datetimepicker.full.min");



$(function () {
    let start = $("#av_start");
    let stop = $("#av_stop");

    let picker_options = {
        lang: 'fr',
        format:'d/m/Y H:i',
    };


    let start_time = new Date(getUrlParameter("start"));
    let stop_time = new Date(getUrlParameter("stop"));
    let now_time = new Date();

    if(!getUrlParameter("start").includes("T")) {
        start_time.setHours(now_time.getHours(), now_time.getMinutes());
        stop_time.setHours(now_time.getHours(), now_time.getMinutes());
        stop_time.setDate(stop_time.getDate()-1);
    }

    start.datetimepicker(picker_options);
    stop.datetimepicker(picker_options);

    start.val(start_time.getDate() + "/" + (start_time.getMonth() + 1) + "/" + start_time.getFullYear() + " " + start_time.getHours() + ":" + start_time.getMinutes());
    stop.val(stop_time.getDate() + "/" + (stop_time.getMonth() + 1) + "/" + stop_time.getFullYear() + " " + stop_time.getHours() + ":" + stop_time.getMinutes());


});

let getUrlParameter = function getUrlParameter(sParam) {
    let sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};