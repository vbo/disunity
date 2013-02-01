(function ($, map) {
    "use strict";

    var Route = function (s1, s2) {
        this.s1 = s1;
        this.s2 = s2;
        this.path = undefined;
        this.highlighted = false;
    };

    Route.prototype.draw = function () {
        var stroke = "#fffc2a";
        if (this.s1.type == 2 || this.s2.type == 2) {
            stroke = "#aba";
        }
        this.path = map.paper.insertElement("path", {d: "M" + j(this.s1) + "L" + j(this.s2), "stroke": stroke, "stroke-width": 1});
    };
    Route.prototype.update = function () {
        this.path.setAttrValues({d: "M" + j(this.s1) + "L" + j(this.s2)});
        var op = 0.8,
            dashar = "1 3";
        if (this.highlighted) {
            op = 1;
            dashar = '4 2';
        }
        this.path.setAttrValues({"stroke-opacity": op, "stroke-dasharray": dashar});

    };

    var j = function (a) {
        return a.x + "," + a.y;
    };

    var Routes = map.Routes = function (neighs, systems) {
        var me = this;

        each(neighs, function(k, pair) {
            var systs = pair.map(function (s) { return systems[s]; });
            me[k] = new Route(systs[0], systs[1]);
        });
    };

    Routes.prototype = new SmartHash(Route, 'update');

    Routes.prototype.draw = function () {
        this.each(function (route) {
            route.draw();
        }, true);
    };

    Routes.prototype.update = function () {
        this.each(function () {}, true);
    };

    Routes.prototype.highlight = function (list) {
        this.setProperty(list, 'highlighted', true);
    };

    Routes.prototype.unhighlight = function (list) {
        this.setProperty(list, 'highlighted', false);
    };

})(jQuery, map);

