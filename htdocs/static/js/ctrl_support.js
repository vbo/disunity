(function ($, map) {
    "use strict";

    var SupportCtrl = map.SupportCtrl = function (systems, data) {
        this.systems = systems;
        this.avail_supports = data['supports'];
        this.bonuses = data['bonuses'];
        this.could_be_supported = data['could_be_supported'];
        this.hud = new map.SupportHud();
        this.supports = null;
    };

    SupportCtrl.prototype.setup = function () {
        var ctrl = this;

        this.hud.eventHidSelected = function (supports) {
            if (ctrl.supports) {
                each(ctrl.supports.orders, function (sid, order) {
                    ctrl.hud.handleSystemCancelled(ctrl.supports, sid);
                });
            }
            ctrl.systems.initSupports(ctrl.avail_supports);
            supports.orders = {};
            ctrl.supports = supports;
            ctrl.systems.eventSupport = function (sys, support) {
                if (ctrl.supports.orders[sys.id]) {
                    ctrl.hud.handleSystemCancelled(supports, sys.id);
                    delete ctrl.supports.orders[sys.id];
                    return;
                }
                supports.orders[sys.id] = {'support': support};
                ctrl.hud.handleSystemSelected(supports, sys.id);
            };
        }

        this.hud.eventSupportDone = function () {
            var data = {'skip': 1};
            if (ctrl.supports && obj_length(ctrl.supports.orders)) {
                data = {
                    'hid': ctrl.supports.hid,
                    'rids': obj_keys(ctrl.supports.orders)
                };
            }
            console.log(data);
            map.api.request("Turn", data, function (r) {
                if (r.result == "success") {
                    return window.location.reload();
                }
                console.log(r);
                alert('see console');
            });
        };

        this.hud.init(this.bonuses, this.could_be_supported);
    };

})(jQuery, map);



