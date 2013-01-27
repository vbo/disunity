(function ($, map) {
    "use strict";

    var hud = map.MarchHud = function () {
        this.eventUnitSelected = null;
        this.eventUnitCancelled = null;
        this.eventSetUnitCancelled = null;
        this.eventMarchesDone = null;
        this.eventPower = null;
        this.eventUnpower = null;
    };

    hud.prototype.init = function () {
        var hud = this;
        $('.hud.bottom').show();
        $('.hud.bottom .power').show().removeClass('used').click(function () {
            var $t = $(this);
            if ($t.hasClass('active')) {
                if ($t.hasClass('used')) {
                    $t.removeClass('used');
                    hud.eventUnpower();
                    return;
                }
                $(this).addClass('used');
                hud.eventPower();
            }
        });
        $('.hud.bottom .done').click(function () {
            if ($(this).hasClass('active')) {
                hud.eventMarchesDone();
            }
        });
        var cnt = $('.hud.bottom .units').html('');
    };

    hud.prototype.handleShowUnits = function (units, could_power) {
        console.log(could_power);
        var hud = this;
        $('.hud.bottom .power').removeClass('active');
        if (could_power) {
            $('.hud.bottom .power').addClass('active');
        }
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
        $('.hud.bottom .done').addClass('active');
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


