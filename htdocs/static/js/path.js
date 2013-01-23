(function() {
    "use strict";

    var $path = window.$path = {};

    $path.bg = function (x, y, max_orb) {
        return $P.circle(x, y, max_orb + 8).attr({"fill": "black"});
    };

    var fort_orb = function (x, y, r, me) {
        return $P.circle(x, y, r).attr({"stroke": me.stroke, "stroke-width": 3, "fill": me.fill});
    };

    $path.orb = function (x, y, r, me) {
        return $P.circle(x, y, r).attr({"stroke": me.orb_stroke, "stroke-width": 1});
    };

    $path.fort_orb_2 = function (x, y, me) {
        return fort_orb(x, y, 15, me);
    };

    $path.fort_orb_3 = function (x, y, me) {
        return fort_orb(x, y, 3, me);
    };

    $path.star = function (x, y, me) {
        var a;
        if (me.type == 1) { // land
            a = $P.circle(x, y, 9);
        } else if (me.type == 2) { // water
            var d = 7, D = 2 * d + 2;
            a = $P.beacon(x, y, d, D);
        } else { // port
            a = $P.square(x, y, 8);
        }
        a.attr({"stroke": me.stroke, "stroke-width": 3, "fill": me.fill});
        return a;
    };

    $path.satellite = function(x, y, color, me) {
        var p = $P.circle(x, y, 4).attr({"fill": color});
        p.dx = x - me.x;
        p.dy = y - me.y;
        return p;
    };

    $path.anchor = function(x, y, max_orb) {
        return $P.circle(x, y, max_orb + 5)
            .attr({"fill": "white", "fill-opacity": 0, "stroke-opacity": 0});
    };

    $path.order = function(x, y, me) {
        return $P.sector(x, y, 42)
            .attr({"fill-opacity": 1, "stroke-opacity": 1, "stroke-width": 1,"fill": "black", "stroke": me.orb_stroke});
    };

    $path.order_glyph = function (x, y, gl, me) {
        return $P.glyph(x + 19, y - 19, gl)
            .attr({ "fill-opacity": 1, "stroke-width": 0, "fill": me.fill});
    };

    $path.order_star = function (x, y, gl, me) {
        return $P.glyph(x + 10, y - 25, gl)
            .attr({ "fill-opacity": 1, "stroke-width": 0, "fill": me.fill});
    };

    $path.army = function(x, y, army_type, me, transparent) {
        transparent = transparent || false;
        var p = (function () {
            if (army_type == 1) {
                return $P.footman(x, y);
            } else if (army_type == 2) {
                return $P.cruiser(x, y);
            } else {
                return $P.robot(x, y);
            }
        })();
        p.attr({"stroke": me.fill, "stroke-width": 1, "fill": me.fill});
        if (transparent) {
            p.attr({"stroke-opacity": 0.5, "fill-opacity": 0.5});
        }
        return p;
    };

    $path.enemy = function(x, y, army_type, me) {
        var p = (function () {
            if (army_type == 1) {
                return $P.footman(x, y);
            } else if (army_type == 2) {
                return $P.cruiser(x, y);
            } else {
                return $P.robot(x, y);
            }
        })();
        return p.attr({"stroke": me.fill, "stroke-width": 1, "fill": me.fill})
    };

    // Useful path generators
    // (We use only paths for consistent translation applying)
    var $P = (function () {
        var join = function (a) {
            return a.join(" ");
        };
        var draw = function (drawer) {
            return function (x, y) {
                return join(drawer(x, y).map(join));
            };
        };
        var _ = function (drawer, x, y) {
            var path = draw(drawer);
            var p = map.paper.path(path(x, y));
            p.redraw = function (x, y) {
                p.attr("path", path(x, y));
                return p;
            };
            return p;
        };
        return {
            glyph: function (x, y, type) {
                var p = glyph[type];
                return _(function (x, y) {
                    return Raphael.transformPath(p, 'T' + x + ' ' + y);
                }, x, y);
            },
            beacon: function (x, y, r, h) {
                return _(function (x, y) { return [
                    ["M", (x - r), (y + r)],
                    ["l", r, -h],
                    ["l", r, h],
                    ["z"]
                ]}, x, y);
            },
            cruiser: function (x, y) {
                var wing = 7;
                var wingh = wing;
                var r = wing / 2;
                var neckh = wing * 1.5;
                var neck = neckh / 4;
                var pad = 3;
                var headh = wing / 1.5;
                var g = 2;
                return _(function (x, y) { return Raphael.transformPath([
                    ["M", x - 5, y + 8],
                    ["l", 2 * wing - 2 * g, 0],
                    ["l", g, -wingh],
                    ["l", -wing + neck / 2 + pad, 0],
                    ["l", 0, wingh / 2],
                    ["l", -pad, 0],
                    ["l", 0, -neckh],
                    ["l", r - neck / 2, 0],
                    ["l", -r / 2, -headh],
                    ["l", -r, 0],
                    ["l", -r / 2, headh],
                    ["l", r - neck / 2, 0],
                    ["l", 0, neckh],
                    ["l", -pad, 0],
                    ["l", 0, -wingh / 2],
                    ["l", -wing + neck / 2 + pad, 0],
                    ["z"]
                ], "r90")}, x, y);
            },
            robot: function (x, y) {
                var hand = 1;
                var body = 5;
                var bodyh = 4;
                var handh = bodyh;
                var head = body;
                var headh = bodyh;
                var pad = 2;

                return _(function (x, y) { return [
                    ["M", x - 4, y + 4],
                    ["l", hand, 0],
                    ["l", 0, -handh],
                    ["l", body - 2 * hand, 0],
                    ["l", 0, handh],
                    ["l", hand, 0],
                    ["l", 0, -handh - bodyh ],
                    ["l", pad, 0],
                    ["l", 0, handh],
                    ["l", hand, 0],
                    ["l", 0, -handh - pad],
                    ["l", -pad - hand, 0],
                    ["l", 0, -headh],
                    ["l", -head, 0],
                    ["l", 0, headh],
                    ["l", -pad - hand, 0],
                    ["l", 0, handh + pad],
                    ["l", hand, 0],
                    ["l", 0, -handh],
                    ["l", pad, 0],
                    ["z"]
                ]}, x, y);
            },
            footman: function (x, y) {
                var r = 5;
                var leg = 2 * r / 3;
                var leg_shift = r / 3;
                return _(function (x, y) { return [
                    ["M", x - r, y],
                    ["l", 2 * r, 0],
                    ["l", 0, -r / 2],
                    ["l", -r, -r / 2],
                    ["l", -r, +r / 2],
                    ["l", 0, +r / 2],
                    ["l", -leg_shift, 0],
                    ["l", 0, leg],
                    ["m", leg_shift + 2 * r, -leg],
                    ["l", leg_shift, 0],
                    ["l", 0, leg],
                    ["m", -r - leg_shift / 2, -leg],
                    ["l", -leg_shift / 2, leg],
                    ["l", -leg_shift / 2, -leg]
                ]}, x, y);
            },
            circle: function (x, y, r) {
                return _(function (x, y) { return [
                    ["M",  x, y],
                    ["m", -r, 0],
                    ["a",  r, r, 0, 1, 0,  r * 2, 0],
                    ["a",  r, r, 0, 1, 0, -r * 2, 0]
                ]}, x, y);
            },
            sector: function (x, y, r) {
                return _(function (x, y) { return [
                    ["M",  x, y],
                    ["l",  r, 0],
                    ["a",  r, r, 0, 0, 0,  -r, -r],
                    ['z']
                ]}, x, y);
            },
            square: function (x, y, r) {
                var R = r * 2;
                return _(function (x, y) { return [
                    ["M", (x - r), (y - r)],
                    ["l", 0, R],
                    ["l", R, 0],
                    ["l", 0, -R],
                    ["z"]
                ]}, x, y);
            }
        };
    })();

})();

