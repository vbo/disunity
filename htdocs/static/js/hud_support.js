(function ($, map) {
    "use strict";

    var hud = map.SupportHud = function () {
        this.eventHidSelected = null;
        this.eventSupportDone = null;
        this.render = {
            'unit': function (bonus) {
                return '<li class="army house_' + bonus.hid +
                        '"><img class="unit" src="/static/img/unit/' + bonus.unit +
                        '.png" /></li>';
            },
            'order': function (bonus) {
                var order = bonus.order;
                var b = number_format(order.bonus);
                var star = order.star ? '<i class="icon-star star"></i>' : '';
                var cls = b || star ? '' : ' common';
                return '<li class="order house_' + order.hid + cls + '"><span class="holder">' +
                        '<i class="icon-' + order.icon + ' icon"></i>' +
                        '<span class="bonus">' + b + '</span>' + star +
                    '</span></li>';
            },
            'homeland': function (bonus) {
                return '<li class="homeland house_' + bonus.hid + '">' + number_format(bonus.bonus) + '</li>';
            }
        };
    };

    hud.prototype.init = function (bonuses, could_be_supported) {
        var hud = this;
        $('.hud.bottom2').show();
        var cnt = $('.hud.bottom2 .fights').html('');
        each(bonuses, function (hid, bonuses) {
            var el = '<ul class="fight"><li class="house house_' + hid + '"><i class="icon-globe"></i></li>';
            var sum = 0;
            bonuses.forEach(function (bonus) {
                el += hud.render[bonus.type](bonus);
                sum += bonus.bonus;
            });
            el += '<li class="result house_' + hid + '">' + sum + '</li>';
            el += '</ul>';
            var $house = $(el);
            cnt.append($house);
            if (could_be_supported[hid]) {
                $house.css('cursor', 'pointer').click(function () {
                    var $t = $(this);
                    if ($t.hasClass('active')) {
                        return;
                    }
                    $t.parent().find('ul').removeClass('active').find('li.support').remove();
                    $house.append('<li class="support house_' + map.current_player['house'] + '"><i class="icon-arrow-left icon"></i></li>');
                    $t.addClass('active');
                    var supports = {'hid': hid, 'node': $t};
                    hud.eventHidSelected(supports);
                });
                if (obj_length(could_be_supported) == 1) {
                    $house.click();
                }
            }
        });

        if (obj_length(could_be_supported)) {
            $('.hud.bottom2 .done').addClass('active').click(function () {
                hud.eventSupportDone();
            });
        }
    };

    hud.prototype.handleSystemSelected = function (supports, sid) {
        var hud = this;
        var nodes = [];
        var sum = 0;
        var order = supports.orders[sid];
        each(order['support'][supports.hid], function (k, bonus) {
            sum += bonus.bonus;
            nodes.push($(hud.render[bonus.type](bonus)));
        });
        order['nodes'] = nodes;
        order['sum'] = sum;
        supports.node.append(nodes);
        var result = supports.node.find('.result');
        result.html(parseInt(result.html()) + sum);
        return nodes;
    };

    hud.prototype.handleSystemCancelled = function (supports, sid) {
        var support = supports.orders[sid];
        var result = supports.node.find('.result');
        result.html(parseInt(result.html()) - support['sum']);
        each(support['nodes'], function (k, node) {
            node.remove();
        });
    };

})(jQuery, map);



