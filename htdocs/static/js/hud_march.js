(function ($, map) {
    "use strict";

    var hud = map.MarchHud = function () {
        this.eventUnitSelected = null;
        this.eventUnitCancelled = null;
        this.eventSetUnitCancelled = null;
        this.eventMarchesDone = null;
    };

    hud.prototype.handleShowUnits = function (units) {
        var hud = this;
        $('.hud.bottom').show();
        var cnt = $('.hud.bottom .units').html('');
        units.forEach(function (u) {
            var $unit = $('<li><img class="unit" src="/static/img/unit/' + u + '.png" /></li>');
            var unit = {type: u, node: $unit};
            cnt.append($unit);
            $unit.click(function () {
                var $t = $(this);
                if ($t.hasClass('active')) {
                    $t.removeClass('active');
                    hud.eventUnitCancelled(unit);
                    return;
                }
                if ($t.hasClass('used')) {
                    $t.removeClass('used');
                    hud.eventSetUnitCancelled(unit);
                }
                $(this).addClass('active');
                hud.eventUnitSelected(unit);
            });
        });

        $('.hud.bottom .done').addClass('active').click(function () {
            hud.eventMarchesDone();
        });
    };

    hud.prototype.handleUnitSet = function (unit) {
        unit.node.removeClass('active').addClass('used');
    };

    hud.prototype.handleReturnUnits = function (units) {
        each(units, function (k, unit) {
            unit.node.click();
            unit.node.click();
        });
    };

})(jQuery, map);


