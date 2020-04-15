require("core-js");
import "../css/global.scss";
import "../css/admin.scss";
import "vis-timeline/styles/vis-timeline-graph2d.css";
import $ from 'jquery';

import {Timeline} from "vis-timeline/peer";
import {DataSet} from "vis-data/peer"


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
                content: user.name,
                skills: user.skills,
                visible: true
            });
            $.getJSON("/admin/user/" + user.username + "/planning", function (planning_els) {
                planning_els.forEach(function (planning_el) {
                    if (planning_el.type === "availability") {
                        planning_el.name = "Disponibilité";
                        planning_el.className = "av_box";
                    } else if (planning_el.type === "event") {
                        planning_el.className = "ev_box";
                    }
                    items.add({
                        id: planning_el.id + user.username,
                        group: user.username,
                        start: new Date(planning_el.start),
                        end: new Date(planning_el.stop),
                        content: planning_el.name,
                        className: planning_el.className
                    });
                });
            });
        });
        let start = new Date();
        start.setHours(0, 0);
        let stop = new Date();
        stop.setHours(24, 0);
        timeline.setWindow(start, stop);
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
                console.log(groups.get(user.id))
            });

        });
    }
});




