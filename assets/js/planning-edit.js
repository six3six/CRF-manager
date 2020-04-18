import '../css/planning.scss';
import $ from 'jquery';
import "jquery-datetimepicker/build/jquery.datetimepicker.min.css";


require("jquery-datetimepicker/build/jquery.datetimepicker.full.min");

var picker_options = {
    lang: 'fr',
    format: 'd/m/Y H:i',
};

$(function () {
    let datepickers = $(".datepicker");
    console.log(datepickers);
    for (let i = 0; i < datepickers.length; i++) {
        let datep = $(datepickers[i]);
        datep.attr("type", 'text');
        datep.datetimepicker(picker_options);
    }

});