(function ($, map) {
    "use strict";

    var hud = map.PlanningHud = function () {
        this.eventOrderSelected = null;
        this.eventOrderCancelled = null;
        this.eventPresetOrderSelected = null;
        this.eventPlanningDone = null;
    };

    hud.prototype.init = function (orders, preset_orders) {
        var cnt = $('.hud.bottom .orders').html('');
        var hud = this;
        each(orders, function (k, order) {
            var bonus = number_format(order.bonus);
            var star = order.star ? '<i class="icon-star star"></i>' : '';
            var cls = bonus || star ? '' : ' common';
            var id = 'order_' + k;
            var $order = $(
                '<li class="' + cls + '" id="' + id + '"><span class="holder">' +
                    '<i class="icon-' + order.icon + ' icon"></i>' +
                    '<span class="bonus">' + bonus + '</span>' + star +
                '</span></li>'
            );
            order.node = $order;
            cnt.append($order);
            var handler = function () {
                var $t = $(this);
                if ($t.hasClass('blocked')) {
                    return;
                }
                if ($t.hasClass('used')) {
                    hud.eventOrderCancelled(order);
                    $t.removeClass('used');
                }
                $t.parent().find('li').removeClass('active');
                $t.addClass('active');
                hud.eventOrderSelected(order);
            };
            $order.click(handler);
            var preset_order = pop_first(preset_orders, function (k, order_id) {
                return order_id == order.id;
            });
            if (preset_order !== null) {
                handler();
                hud.eventPresetOrderSelected(order);
            }
        });
        $('.hud.bottom .done').click(function () {
            if ($(this).hasClass('active')) {
                hud.eventPlanningDone();
            }
        });
    };

    hud.prototype.handleUnsetOrder = function (order) {
        order.node.removeClass('used');
    };

    hud.prototype.handleCurOrderSet = function (order) {
        order.node.removeClass('active').addClass('used');
    };

    hud.prototype.block_star_orders = function () {
        $('.hud.bottom .orders').find('li .star').closest('li').each(function () {
            if (!$(this).hasClass('used')) {
                $(this).addClass('blocked');
            }
        });
    };
    hud.prototype.unblock_star_orders = function () {
        $('.hud.bottom .orders li').removeClass('blocked');
    };
    hud.prototype.block_orders_done = function () {
        $('.hud.bottom .done').removeClass('active');
    };
    hud.prototype.unblock_orders_done = function () {
        $('.hud.bottom .done').addClass('active');
    };

})(jQuery, map);

