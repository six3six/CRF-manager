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
        zoomKey: "ctrlKey"
    };
    let groups = new DataSet();
    let items = new DataSet();

    $.getJSON("/admin/user_list", function (users) {
        users.forEach(function (user) {
            groups.add({
                id: user.username,
                content: user.name
            });
            $.getJSON("/admin/user_planning/" + user.username, function (planning_els) {
                planning_els.forEach(function (planning_el) {
                    console.log(planning_el.type === "availability");
                    if (planning_el.type === "availability"){
                        planning_el.name = "Disponibilité";
                        planning_el.className = "av_box";
                    } else if (planning_el.type === "event") {
                        planning_el.className = "ev_box";
                    }
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
        })
    });
    /*
        for (let i = 0; i < count; i++) {
            let start = now + 1000 * 60 * 60 * 24 * (i + Math.floor(Math.random() * 7))
            let end = start + 1000 * 60 * 60 * 24 * (1 + Math.floor(Math.random() * 5))

            groups.add({
                id: i,
                content: 'Task ' + i,
                order: i
            });

            items.add({
                id: i,
                group: i,
                start: start,
                end: end,
                type: 'range',
                content: 'Item ' + i
            });
        }
    */
    // create a Timeline
    let container = document.getElementById('visualization');
    let timeline = new Timeline(container, null, options);
    timeline.setGroups(groups);
    timeline.setItems(items);

});




