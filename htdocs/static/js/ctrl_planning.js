(function ($, map) {
    "use strict";

    var PlanningCtrl = map.PlanningCtrl = function (systems, data) {
        this.systems = systems;
        this.hud = new map.PlanningHud();
        this.to_be_ordered = data['regions'];
        this.other_orders = data['other_orders'];
        this.available_orders = data['available_orders'];
        this.player_orders = data['player_orders'];
        this.stars = data['stars'];

        this.set_orders = {};
    };

    PlanningCtrl.prototype.setup = function () {
        var ctrl = this;
        this.systems.initPlanning(this.to_be_ordered, this.other_orders);

        this.hud.eventOrderSelected = function (order) {
            var on_click = function (clb) {
                return ctrl.systems.setupPlanningOnClick(ctrl.to_be_ordered, clb);
            };
            on_click(function () {
                if (this.order != -1) {
                    ctrl.hud.handleUnsetOrder(this.order);
                    delete ctrl.set_orders[this.id];
                }
                ctrl.set_orders[this.id] = order;
                this.setOrder(order);
                order.sys = this;
                ctrl.hud.handleCurOrderSet(order);
                ctrl.handleSetOrdersChanged();
                on_click(null);
            });
        };

        this.hud.eventOrderCancelled = function (order) {
            var sys = order.sys;
            sys.unsetOrder();
            delete order.sys;
            delete ctrl.set_orders[sys.id];
            ctrl.handleSetOrdersChanged();
        };

        this.hud.eventPlanningDone = function () {
            var data = reachs(ctrl.set_orders, function (k, order) {
                return order.id;
            });
            map.api.request("Turn", {orders: data}, function (r) {
                console.log("resp");
                if (r.result == "success") {
                    console.log("suc");
                    return window.location.reload();
                }
                console.log("faile");
                alert('see console');
            });
        };

        this.hud.eventPresetOrderSelected = function (order) {
            var sys_id = pop_first(ctrl.player_orders, function (sys_id, ord) {
                return ord.id == order.id;
            }, true);

            ctrl.systems[sys_id].order_click();
        };

        var preset_orders = reach(this.player_orders, function (sid_id, order) {
            return order.id;
        });

        this.hud.init(ctrl.available_orders, preset_orders);
    };

    PlanningCtrl.prototype.handleSetOrdersChanged = function () {
        var stars_used = count_reduce(this.set_orders, function (order) {
            return order.star;
        });
        if (stars_used < this.stars) {
            this.hud.unblock_star_orders();
        } else {
            this.hud.block_star_orders();
        }
        var set_orders_count = obj_length(this.set_orders);
        if (set_orders_count < this.to_be_ordered.length) {
            this.hud.block_orders_done();
        } else {
            this.hud.unblock_orders_done();
        }
    };

})(jQuery, map);

