$(document).ready(function () {
    // TODO: remove overscroll.js. do the photoshop-like scrolling
    // and zooming by hand via paper.setViewBox()
    // See http://jsfiddle.net/9zu4U/10/
    var overscroll = false;
    $(document).bind("keydown", "space", function (evt) {
        if (!overscroll) {
            overscroll = true;
            var opts = {showThumbs: false, wheelDelta: 60, dragHold: true};
            $('.map_holder').overscroll(opts);
        }
    });
    $(document).bind("keyup", "space", function (evt) {
        overscroll = false;
        $('.map_holder').removeOverscroll();
    });
    var on_resize = function (left, top) {
        var opts = {showThumbs: false, wheelDelta: 60, dragHold: true};
        if (left > 0) {
            opts.scrollLeft = left;
        }
        if (top > 0) {
            opts.scrollTop = top;
        }
        $('.map_holder').width($(window).width());
        $('.map_holder').height($(window).height());
        $('.map_holder').overscroll(opts);
        $('.map_holder').removeOverscroll();
    };
    $(window).resize(on_resize);
    on_resize(19980, 19940);
});

