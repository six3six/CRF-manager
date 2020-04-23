import "../css/global.scss";
import "../css/admin.scss";
import "vis-timeline/styles/vis-timeline-graph2d.css";
import $ from 'jquery';

import {Timeline} from "vis-timeline/peer";
import {DataSet} from "vis-data/peer"
import feather from "feather-icons"

import '../css/planning.scss';
import "jquery-datetimepicker/build/jquery.datetimepicker.min.css";

require("jquery-datetimepicker/build/jquery.datetimepicker.full.min");


let picker_options = {
    lang: 'fr',
    format: 'd/m/Y',
    timepicker: false,
};


let user_profile_url = "/admin/user/"
$(function () {

    let now = Date.now();

    let options = {
        stack: true,
        horizontalScroll: true,
        verticalScroll: true,
        zoomKey: "ctrlKey",
    };
    let groups = new DataSet();
    let items = new DataSet();

    $.getJSON("/admin/users/json", function (users) {
        users.forEach(function (user) {
            groups.add({
                id: user.username,
                content: "<a href='" + user_profile_url + user.username + "' target=\"_blank\">" + user.name + "</a>",
                skills: user.skills,
                visible: true
            });
            $.getJSON("/admin/user/" + user.username + "/planning", function (planning_els) {
                planning_els.forEach(function (planning_el) {
                    items.add({
                        id: planning_el.id,
                        group: user.username,
                        start: new Date(planning_el.start),
                        end: new Date(planning_el.stop),
                        content: planning_el.name,
                        className: planning_el.className
                    });
                });
            });
        });

        focusOnDate(current_date);
        date_picker.val(getTextFromDate(current_date));
        console.log(groups);
    });
    // create a Timeline
    let container = document.getElementById('visualization');
    let timeline = new Timeline(container, null, options);
    timeline.setGroups(groups);
    timeline.setItems(items);

    let skills_selected = [];
    let check_boxes = $(".filter-check");
    for (let i = 0; i < check_boxes.length; i++) {
        let check_box = $(check_boxes[i]);


        check_boxes.click(function (event) {
            let id = event.currentTarget.id.split("_")[1];
            skills_selected[id] = $(event.currentTarget).is(':checked');

            groups.forEach(function (user) {
                let visible = true;
                skills_selected.forEach(function (skill_act, skill_id) {
                    console.log(skill_id in user.skills)
                    if (skill_act && !(skill_id in user.skills)) visible = false;
                })
                groups.update({'id': user.id, "visible": visible});
                console.log(groups.get(user.id));
            });

        });
    }

    timeline.on("rangechanged", function (properties) {
        current_date = properties.start;
        date_picker.val(getTextFromDate(current_date));
    });

    let date_picker = $("#datepicker");
    console.log(date_picker);

    let current_date = new Date();
    date_picker.datetimepicker(picker_options);
    date_picker.change(function (event) {
        let value = $(event.currentTarget).val();
        if (value === "") return;
        current_date = getDateFromVal(value);
        focusOnDate(current_date);
    });

    let focusOnDate = function (date) {
        let start = new Date(date);
        let stop = new Date(date);
        start.setHours(0, 1, 0);
        stop.setHours(23, 59, 0);
        timeline.setWindow(start, stop);
    }

    let getDateFromVal = function (value) {
        let v = value.split("/");
        let date = new Date();
        date.setFullYear(v[2], Number(v[1]) - 1, v[0]);
        return date;
    }

    let getTextFromDate = function (date) {
        let formatNumber = function (number, size = 2) {
            let str = "" + number;
            while (str.length < size) {
                str = "0" + str;
            }
            return str;
        }
        return formatNumber(date.getDate()) + "/" + formatNumber(date.getMonth() + 1) + "/" + date.getFullYear();
    }

    let changeDay = function (event) {
        if (event.currentTarget.id === "today") {
            current_date = new Date();
        } else if (event.currentTarget.id === "addDay") {
            current_date.setDate(current_date.getDate() + 1);
        } else {
            current_date.setDate(current_date.getDate() - 1);
        }
        date_picker.val(getTextFromDate(current_date));
        focusOnDate(current_date);
    }

    $("#addDay").click(changeDay);
    $("#remDay").click(changeDay);
    $("#today").click(changeDay);

    feather.replace();
});





