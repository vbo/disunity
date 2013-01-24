(function ($, map) {
    "use strict";

    var MarchCtrl = map.MarchCtrl = function (systems, routes, data) {
        this.systems = systems;
        this.routes = routes;
        this.available_routes = data['routes'];
        this.hud = new map.MarchHud();

        this.march = null;
    };

    MarchCtrl.prototype.setup = function () {
        var ctrl = this;

        this.systems.eventMarchOrderSelected = function (march) {
            if (ctrl.march) {
                if (ctrl.march == march) {
                    ctrl.hud.handleReturnUnits(march.sys.sent_units);
                    return;
                }
                ctrl.march.sys.sent_units = null;
                ctrl.march.sys.update();
                ctrl.routes.unhighlight(ctrl.routesToHighlight(ctrl.march.routes));
                ctrl.systems.marchHereSystemsReset(obj_keys(ctrl.march.routes));
            }
            ctrl.march = march;
            ctrl.routes.highlight(ctrl.routesToHighlight(march.routes));
            ctrl.hud.handleShowUnits(march.sys.army);

            march.selected_units = [];
            ctrl.systems.eventMarchHere = function (sys) {
                each(march.selected_units, function (k, unit) {
                    ctrl.hud.handleUnitSet(unit);
                    unit.homesys = march.sys;
                    unit.sys = sys;
                    sys.units.push(unit);
                    march.sys.sent_units.push(unit);
                });
                march.sys.update();
                march.selected_units = [];
            };
            ctrl.systems.setCouldBeMarchHere(obj_keys(march.routes));
            ctrl.hud.eventUnitSelected = function (unit) {
                march.selected_units.push(unit);
            };
            ctrl.hud.eventUnitCancelled = function (unit) {
                pop_first(march.selected_units, function (k, u) {
                    return unit.type == u.type;
                });
            };
            ctrl.hud.eventSetUnitCancelled = function (unit) {
                pop_first(unit.sys.units, function (k, u) {
                    return unit.type == u.type;
                });
                unit.sys.update();
                pop_first(march.sys.sent_units, function (k, u) {
                    return unit.type == u.type;
                });
                march.sys.update();
            };
            ctrl.hud.eventMarchesDone = function () {
                var homesys = march.sys;
                var data = {'source': homesys.id};
                if (!homesys.sent_units.length) {
                    data['skip'] = true;
                }
                // todo: implement stay power at homesys
                data['power'] = 0;
                var marches = {};
                each(homesys.sent_units, function (k, unit) {
                    if (!marches[unit.sys.id]) {
                        marches[unit.sys.id] = [];
                    }
                    marches[unit.sys.id].push(unit.type);
                });
                data['marches'] = marches;

                console.log(data);
                map.api.request("Turn", data, function (r) {
                    if (r.result == "success") {
                        return window.location.reload();
                    }
                    console.log(r);
                    alert('see console');
                });
            };
        };

        this.systems.initMarches(this.available_routes);
    };

    MarchCtrl.prototype.routesToHighlight = function (routes) {
        var routes_highlighted = {};
        each(routes, function (k, target) {
            var points = target.route;
            for (var i = 0; i < points.length - 1; i++) {
                var pair = [points[i], points[i + 1]].sort().join('_');
                routes_highlighted[pair] = true;
            }
        });
        return obj_keys(routes_highlighted);
    };
})(jQuery, map);


