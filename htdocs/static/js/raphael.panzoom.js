/**
 * raphael.pan-zoom plugin 0.2.0
 * Copyright (c) 2012 @author Juan S. Escobar
 * https://github.com/escobar5
 *
 * licensed under the MIT license
 */
(function ($) {

    Raphael.fn.panzoom = {};

    Raphael.fn.panzoom = function (options) {
        var paper = this;
        return new PanZoom(paper, options);
    };

    var panZoomFunctions = {
        enable: function () {
            this.enabled = true;
        },

        disable: function () {
            this.enabled = false;
        },

        zoomIn: function (steps) {
            this.applyZoom(steps);
        },

        zoomOut: function (steps) {
            this.applyZoom(steps > 0 ? steps * -1 : steps);
        },

        pan: function (deltaX, deltaY) {
        },

        isDragging: function () {
            return this.dragTime > this.dragThreshold;
        },

        getCurrentPosition: function () {
            return this.currPos;
        },

        getCurrentZoom: function () {
            return this.currZoom;
        }
    },

    PanZoom = function (el, options) {
        var paper = el,
            container = paper.canvas.parentNode,
            me = this,
            settings = {},
            initialPos = { x: 0, y: 0 },
            deltaX = 0,
            deltaY = 0,
            mousewheelevt = (/Firefox/i.test(navigator.userAgent)) ? "DOMMouseScroll" : "mousewheel";

        this.enabled = false;
        this.dragThreshold = 5;
        this.dragTime = 0;

        options = options || {};

        settings.maxZoom = options.maxZoom || 9;
        settings.minZoom = options.minZoom || 0;
        settings.zoomStep = options.zoomStep || 0.1;
        settings.initialZoom = options.initialZoom || 0;
        settings.initialPosition = options.initialPosition || { x: 0, y: 0 };

        this.currZoom = settings.initialZoom;
        this.currPos = settings.initialPosition;

        repaint();

        container.onmousedown = function (e) {
            var evt = window.event || e;
            if (!me.enabled) return false;
            me.dragTime = 0;
            initialPos = getRelativePosition(evt, container);
            container.className += " grabbing";
            container.onmousemove = dragging;
            document.onmousemove = function () { return false; };
            if (evt.preventDefault) evt.preventDefault();
            else evt.returnValue = false;
            return false;
        };

        container.onmouseup = function (e) {
            //Remove class framework independent
            document.onmousemove = null;
            container.className = container.className.replace(/(?:^|\s)grabbing(?!\S)/g, '');
            container.onmousemove = null;
        };

        $(container).bind("mousewheel", handleScroll);

        function handleScroll(e, delta) {
            if (!me.enabled) return false;
            var evt = window.event || e,
                zoomCenter = getRelativePosition(evt, container);

            applyZoom(delta, zoomCenter);
            if (evt.preventDefault) evt.preventDefault();
            else evt.returnValue = false;
            return false;
        }

        function applyZoom(val, centerPoint) {
            if (!me.enabled) return;
            if ( me.currZoom == settings.minZoom && val < 0 || me.currZoom == settings.maxZoom && val > 0) {
                return;
            }
            var probableZoom = me.currZoom + val;
            if (probableZoom < settings.minZoom) {
                val = settings.minZoom - me.currZoom;
                me.currZoom = settings.minZoom;
            } else if (probableZoom > settings.maxZoom) {
                val = settings.maxZoom - me.currZoom;
                me.currZoom = settings.maxZoom;
            } else {
                me.currZoom = probableZoom;
            }
            centerPoint = centerPoint || { x: paper.width/2, y: paper.height/2 };
            deltaX = (paper.width  * settings.zoomStep) * (centerPoint.x / paper.width) * val;
            deltaY = (paper.height * settings.zoomStep) * (centerPoint.y / paper.height) * val;
            repaint();
        }

        this.applyZoom = applyZoom;

        function dragging(e) {
            if (!me.enabled) return false;
            var evt = window.event || e,
                newWidth = paper.width * (1 - (me.currZoom * settings.zoomStep)),
                newHeight = paper.height * (1 - (me.currZoom * settings.zoomStep)),
                newPoint = getRelativePosition(evt, container);

            deltaX = (newWidth * (newPoint.x - initialPos.x) / paper.width) * -1;
            deltaY = (newHeight * (newPoint.y - initialPos.y) / paper.height) * -1;
            initialPos = newPoint;

            repaint();
            me.dragTime++;
            if (evt.preventDefault) evt.preventDefault();
            else evt.returnValue = false;
            return false;
        }

        function repaint() {
            me.currPos.x = me.currPos.x + deltaX;
            me.currPos.y = me.currPos.y + deltaY;

            var newWidth = paper.width * (1 - (me.currZoom * settings.zoomStep)),
                newHeight = paper.height * (1 - (me.currZoom * settings.zoomStep));

            paper.setViewBox(me.currPos.x, me.currPos.y, newWidth, newHeight);
        }
    };

    PanZoom.prototype = panZoomFunctions;

    function getRelativePosition(e, obj) {
        var x,y, pos;
        if (e.pageX || e.pageY) {
            x = e.pageX;
            y = e.pageY;
        }
        else {
            x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

        pos = findPos(obj);
        x -= pos[0];
        y -= pos[1];

        return { x: x, y: y };
    }

    function findPos(obj) {
        var posX = obj.offsetLeft, posY = obj.offsetTop, posArray;
        while (obj.offsetParent) {
            if (obj == document.getElementsByTagName('body')[0]) { break; }
            else {
                posX = posX + obj.offsetParent.offsetLeft;
                posY = posY + obj.offsetParent.offsetTop;
                obj = obj.offsetParent;
            }
        }
        posArray = [posX, posY];
        return posArray;
    }

})(jQuery);

