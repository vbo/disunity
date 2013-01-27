(function ($, map) {
    "use strict";

    var RetreatCtrl = map.RetreatCtrl = function (systems, routes, data) {
        this.systems = systems;
        this.routes = routes;
        this.homesys = data['from'];
        this.avail_routes = data['routes'];
        this.hud = new map.RetreatHud();

        this.sys = null;
    };

    RetreatCtrl.prototype.setup = function () {
        var ctrl = this;

        this.routes.highlight(this.routesToHighlight(this.avail_routes));

        this.systems.eventRetreat = function (sys) {
            if (ctrl.sys) {
                ctrl.systems.handleRetreatCancelled(ctrl.sys);
            }
            ctrl.sys = sys;
            ctrl.hud.handleDoneEnable();
        };

        this.hud.eventDone = function () {
            var data = {
                'rid': ctrl.sys.id
            };
            console.log(data);
            map.api.request("Turn", data, function (r) {
                if (r.result == "success") {
                    return window.location.reload();
                }
                console.log(r);
                alert('see console');
            });
        };

        this.systems.initRetreat(this.homesys, obj_keys(this.avail_routes));
    };

    RetreatCtrl.prototype.routesToHighlight = function (routes) {
        console.log(routes);
        var routes_highlighted = {};
        each(routes, function (k, points) {
            for (var i = 0; i < points.length - 1; i++) {
                var pair = [points[i], points[i + 1]].sort().join('_');
                routes_highlighted[pair] = true;
            }
        });
        return obj_keys(routes_highlighted);
    };

})(jQuery, map);




