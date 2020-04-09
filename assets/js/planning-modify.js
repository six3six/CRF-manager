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
        format: 'd/m/Y H:i',
    };

    start.datetimepicker(picker_options);
    stop.datetimepicker(picker_options);

});