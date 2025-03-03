import '../css/planning.scss';
import "jquery-datetimepicker/build/jquery.datetimepicker.min.css";

require("jquery-datetimepicker/build/jquery.datetimepicker.full");


var picker_options = {
    lang: 'fr',
    format: 'd/m/Y H:i',
};

var date_only_picker_options = {
    lang: 'fr',
    format: 'd/m/Y',
};

$(function () {
    let date_pickers = $(".datepicker");
    date_pickers.attr("type", 'text');
    date_pickers.datetimepicker(picker_options);

    let date_only_pickers = $(".datepicker-d");
    date_only_pickers.attr("type", 'text');
    date_only_pickers.datetimepicker(date_only_picker_options);

    let is_event = $(".is-event");

    let change_resp_type = function () {
        let event_fields = $(".event-field");
        let event_fields_parent = event_fields.parent();
        if (is_event.is(":checked")) {
            event_fields_parent.show();
            event_fields.attr("required", true);
        } else {
            event_fields.removeAttr('required');
            event_fields_parent.hide();
        }
    }

    is_event.change(change_resp_type);
    change_resp_type();
});

