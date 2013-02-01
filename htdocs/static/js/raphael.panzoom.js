(function ($) {

    var PanZoom = window.PanZoom = function (svg, options) {
        this.svg = svg;
        this.svg.setAttribute('preserveAspectRatio', 'xMinYMin');
        this.container = this.svg.parentNode;
        this.maxZoom = options.maxZoom || 9;
        this.minZoom = options.minZoom || 0;
        this.zoomStep = options.zoomStep || 0.1;
        this.currZoom = options.initialZoom || 0;
        this.currPos = options.initialPosition || { x: 0, y: 0 };
        this.enabled = false;
        this.dragThreshold = 5;
        this.dragTime = 0;
        this.deltaX = 0;
        this.deltaY = 0;
    };

    PanZoom.prototype.init = function () {
        var me = this;
        this.container.onmousedown = function (e) { return me.onmousedown(e); };
        this.container.onmouseup = function (e) { return me.onmouseup(e); };
        $(this.container).bind("mousewheel", function (e, delta) { return me.handleScroll(e, delta); });
        this.repaint();
    };

    PanZoom.prototype.onmousedown = function (e) {
        var me = this;
        var evt = window.event || e;
        this.dragTime = 0;
        this.initialPos = this.getRelativePosition(evt, this.container);
        this.container.className += " grabbing";
        this.container.onmousemove = function () { return me.dragging(e); };
        document.onmousemove = function () { return false; };
        if (evt.preventDefault) evt.preventDefault();
        else evt.returnValue = false;
        return false;
    };

    PanZoom.prototype.onmouseup = function (e) {
        //Remove class framework independent
        document.onmousemove = null;
        this.container.className = this.container.className.replace(/(?:^|\s)grabbing(?!\S)/g, '');
        this.container.onmousemove = null;
    };

    PanZoom.prototype.handleScroll = function (e, delta) {
        var evt = window.event || e;
        this.applyZoom(delta, this.getRelativePosition(evt, this.container));
        if (evt.preventDefault) evt.preventDefault();
        else evt.returnValue = false;
        return false;
    };

    PanZoom.prototype.applyZoom = function (val, centerPoint) {
        if (this.currZoom == this.minZoom && val < 0 || this.currZoom == this.maxZoom && val > 0) {
            return;
        }
        var probableZoom = this.currZoom + val;
        if (probableZoom < this.minZoom) {
            val = this.minZoom - this.currZoom;
            this.currZoom = this.minZoom;
        } else if (probableZoom > this.maxZoom) {
            val = this.maxZoom - this.currZoom;
            this.currZoom = this.maxZoom;
        } else {
            this.currZoom = probableZoom;
        }
        centerPoint = centerPoint || { x: this.svg.getAttribute('width') / 2, y: this.svg.getAttribute('height') / 2 };
        this.deltaX = (this.svg.getAttribute('width')  * this.zoomStep) * (centerPoint.x / this.svg.getAttribute('width')) * val;
        this.deltaY = (this.svg.getAttribute('height') * this.zoomStep) * (centerPoint.y / this.svg.getAttribute('height')) * val;
        this.repaint();
    };

    PanZoom.prototype.dragging = function (e) {
        var evt = window.event || e;
        var newWidth = this.svg.getAttribute('width') * (1 - (this.currZoom * this.zoomStep));
        var newHeight = this.svg.getAttribute('height') * (1 - (this.currZoom * this.zoomStep));
        var newPoint = this.getRelativePosition(evt, this.container);

        this.deltaX = (newWidth * (newPoint.x - this.initialPos.x) / this.svg.getAttribute('width')) * -1;
        this.deltaY = (newHeight * (newPoint.y - this.initialPos.y) / this.svg.getAttribute('height')) * -1;
        this.initialPos = newPoint;

        this.repaint();
        this.dragTime++;
        if (evt.preventDefault) evt.preventDefault();
        else evt.returnValue = false;
        return false;
    };

    PanZoom.prototype.repaint = function () {
        this.currPos.x = this.currPos.x + this.deltaX;
        this.currPos.y = this.currPos.y + this.deltaY;

        var w = this.svg.getAttribute('width') * (1 - (this.currZoom * this.zoomStep)),
            h = this.svg.getAttribute('height') * (1 - (this.currZoom * this.zoomStep));

        var x = this.currPos.x;
        var y = this.currPos.y;
        var S = " ";
        var vb = x + S + y + S + w + S + h;

        if (x == null) {
            vb = "0 0 " + this.svg.getAttribute('width') + S + this.svg.getAttribute('height');
        }

        this.svg.setAttribute('viewBox', vb);
    };

    PanZoom.prototype.zoomIn = function (steps) {
        this.applyZoom(steps);
    };

    PanZoom.prototype.zoomOut = function (steps) {
        this.applyZoom(steps > 0 ? steps * -1 : steps);
    };

    PanZoom.prototype.pan = function (deltaX, deltaY) {
    };

    PanZoom.prototype.isDragging = function () {
        return this.dragTime > this.dragThreshold;
    };

    PanZoom.prototype.getCurrentPosition = function () {
        return this.currPos;
    };

    PanZoom.prototype.getCurrentZoom = function () {
        return this.currZoom;
    };

    PanZoom.prototype.getRelativePosition = function (e, obj) {
        var x,y, pos;
        if (e.pageX || e.pageY) {
            x = e.pageX;
            y = e.pageY;
        } else {
            x = e.clientX;
            y = e.clientY;
        }

        return { x: x, y: y };
    };

})(jQuery);

