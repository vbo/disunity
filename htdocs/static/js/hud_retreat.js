(function ($, map) {
    "use strict";

    var hud = map.RetreatHud = function () {
        this.eventDone = null;
    };

    hud.prototype.handleDoneEnable = function () {
        var hud = this;
        $('.hud.bottom').show();
        $('.hud.bottom .done').addClass('active').click(function() {
            hud.eventDone();
        });
    };

})(jQuery, map);


