require("core-js");
import "../css/global.scss";
import "vis-timeline/styles/vis-timeline-graph2d.css";
import $ from 'jquery';

import {Timeline} from "vis-timeline/peer";
import {DataSet} from "vis-data/peer"


$(function () {

    let showVisibleGroups = (function () {
        let a = timeline.getVisibleGroups();
        document.getElementById("visibleGroupsContainer").innerHTML = "";
        document.getElementById("visibleGroupsContainer").innerHTML += a;
    });

    let now = Date.now();

    let options = {
        stack: true,
        maxHeight: 640,
        horizontalScroll: false,
        verticalScroll: true,
        zoomKey: "ctrlKey",
        start: Date.now() - 1000 * 60 * 60 * 24 * 3, // minus 3 days
        end: Date.now() + 1000 * 60 * 60 * 24 * 21, // plus 1 months aprox.
        orientation: {
            axis: "both",
            item: "top"
        },
    };
    let groups = new DataSet();
    let items = new DataSet();

    let count = 300;

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

    // create a Timeline
    let container = document.getElementById('visualization');
    let timeline = new Timeline(container, null, options);
    timeline.setGroups(groups);
    timeline.setItems(items);

    function debounce(func, wait = 100) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(this, args);
            }, wait);
        };
    }

    let groupFocus = (e) => {
        let vGroups = timeline.getVisibleGroups();
        let vItems = vGroups.reduce((res, groupId) => {
            let group = timeline.itemSet.groups[groupId];
            if (group.items) {
                res = res.concat(Object.keys(group.items))
            }
            return res
        }, []);
        timeline.focus(vItems)
    };
    this.timeline.on("scroll", debounce(groupFocus, 200))
});




