(function ($, map) {
    "use strict";

    var System = function (id, conf) {
        this.id = id;
        this.owner = conf['owner'];
        this.fort = conf.fort;
        this.order = conf.order;
        this.units = null;
        this.enemy = conf.enemy;
        this.enemy_house = conf.enemyHouse;
        this.order_click = null;
        this.click = null;
        this.sources = conf['crowns'] || 0;
        this.supplies = conf.supplies || 0;
        this.conf = conf;
        this.neighs = conf.neighs;
        this.type = conf.type;
        this.title = conf.name + "(" + id + ")";
        this.x = conf.style.x;
        this.y = conf.style.y;
        var orbs_default = [22];
        if (this.sources > 0 || this.supplies > 0) {
            orbs_default.push(32);
        }
        this.orbs = conf.style.orbs || orbs_default;
        this.fill = conf.style.fill || '#000';
        this.stroke = conf.style.stroke || '#111';

        this.army = [];

        this.bg = undefined;
        this.star = undefined;
        this.anchor = undefined;
        this._orbs = [];
        this.fort_orbs = [];
        this._title = undefined;
        this._army = [];
        this._units = [];
        this._enemy = [];
        this._planets = [];
        this._order = undefined;
        this._order_anchor = undefined;
        this._order_glyph = undefined;
        this._order_star = undefined;
        this._order_bonus = undefined;

        this.orb_stroke = '#005571';
    };

    System.prototype.draw = function () {
        if (this.fort > 1) {
            var fort = this.fort;
            this.orbs = this.orbs.map(function (v) { return v + fort * 3 });
        }
        var max_orb = Math.max.apply(null, this.orbs),
            x = this.x,
            y = this.y;
        this.bg = $path.bg(x, y, max_orb);
        if (this.fort >= 2) {
            this.fort_orbs.push($path.fort_orb_2(x, y, this));
        }
        var me = this;
        this.star = $path.star(x, y, this);
        if (this.fort == 3) {
            this.fort_orbs.push($path.fort_orb_3(x, y, this));
        }
        var buf = this._orbs;
        this.orbs.forEach(function (orb) {
            var o = $path.orb(x, y, orb, me);
            buf.push(o);
        });
        var orb = this.orbs[0];
        var angle = 90 + 30;
        var container = this._planets;
        var planets = function (cnt, color) {
            for (var i = 0; i < cnt; i++) {
                container.push(me.satellite(orb, angle, function (x, y) {
                    return $path.satellite(x, y, color, me);
                }));
                angle += 27;
            }
        };
        planets(this.sources, "yellow");
        planets(this.supplies, "green");
        this._title = map.paper.text(x, y + max_orb + 11, this.title).attr({"fill": '#005571', 'font-weight': 'bold'});
        this.anchor = $path.anchor(x, y, max_orb);
        this.anchor.system = this;

        this.anchor.click(function (evt) {
            if (me.click) {
                me.click(evt);
            }
        });
    };

    System.prototype.satellite = function (orb, angle, draw) {
        var rad = map.paper.raphael.rad(angle);
        return draw(Math.round(this.x + orb * Math.cos(rad)), Math.round(this.y - orb * Math.sin(rad)));
    };

    System.prototype.update = function () {
        // additionally updates config
        var max_orb = Math.max.apply(null, this.orbs),
            x = this.conf.style.x = this.x,
            y = this.conf.style.y = this.y,
            me = this;
        this.bg.redraw(x, y);
        this.star.redraw(x, y).attr({"stroke": this.stroke, "fill": this.fill});
        this._title.attr({"x": x, "y": y + max_orb + 11});
        this.anchor.redraw(x, y);
        this._orbs.forEach(function (o) {
            o.redraw(x, y);
        });
        if (this.fort_orbs) {
            this.fort_orbs.forEach(function (o) {
                o.redraw(x, y).attr({"stroke": me.stroke, "fill": me.fill});
            });
        }
        if (this._order) {
            this._order.remove();
            this._order_anchor.remove();
            if (this._order_glyph) {
                this._order_glyph.remove();
                this._order_bonus.remove();
            }
            if (this._order_star) {
                this._order_star.remove();
            }
        }
        if (this.order) {
            this._order = $path.order(x, y, me);
            if (this.order.could_be_selected) {
                this._order.attr({'stroke': '#0033aa', 'fill': '#001122'});
                if (this.order.selected) {
                    this._order.attr({'fill': '#001144'});
                }
            }

            if (this.order != -1) {
                if (this.order.icon) {
                    this._order_glyph = $path.order_glyph(x, y, this.order.icon, me);
                    if (this.order.star) {
                        this._order_star = $path.order_star(x, y, "star", me);
                    }
                    var str_bonus = number_format(this.order['bonus']);
                    this._order_bonus = map.paper.text(x + 33, y - 8, str_bonus).attr("fill", this.fill);
                }
            }

            this._order_anchor = $path.order(x, y, me).attr({"stroke-opacity": 0, "fill-opacity": 0});
            this._order_anchor.click(function (evt) {
                if (me.order_click) {
                    me.order_click(evt);
                }
            });
        }
        this._planets.forEach(function (p) {
            p.redraw(x + p.dx, y + p.dy);
        });
        if (!map.editable) {
            // Remove army from map editor (too complicated)
            this._army.forEach(function (a) {
                a.remove();
            });
            var orb = this.orbs[this.orbs.length - 1],
                angle = 180 + 30;
            var sent_units = reach(this.sent_units, function (k, unit) {
                return unit.type;
            });
            this.army.forEach(function (unit_type) {
                var sent = pop_first(sent_units, function (k, u) {
                    return unit_type == u;
                });
                me._army.push(
                    me.satellite(orb, angle, function (x, y) {
                        return $path.army(x, y, unit_type, me, sent);
                    })
                );
                angle += 50;
            });
            this._units.forEach(function (a) {
                a.remove();
            });
            orb = max_orb + 16;
            angle = 90 + 30;
            if (this.units) {
                this._units = [];
                each(this.units, function (k, unit) {
                    me._units.push(
                        me.satellite(orb, angle, function (x, y) {
                            return $path.enemy(x, y, unit.type, unit.homesys);
                        })
                    );
                    angle += 30;
                });
            }
            this._enemy.forEach(function (a) {
                a.remove();
            });
            orb = max_orb + 16;
            angle = 90 + 30;
            if (this.enemy) {
                this._enemy = [];
                each(this.enemy, function (k, unit) {
                    me._enemy.push(
                        me.satellite(orb, angle, function (x, y) {
                            return $path.enemy(x, y, unit, map.state.players[me.enemy_house].style);
                        })
                    );
                    angle += 30;
                });
            }
        }
    };

    System.prototype.setOrder = function (order) {
        this.order = order;
        this.update();
    };

    System.prototype.unsetOrder = function () {
        this.order = -1;
        this.update();
    };

    var Systems = map.Systems = function (players, map_config, neighs) {
        var me = this;
        each(map_config, function(sid, sys_config) {
            sid = parseInt(sid);
            var sys = new System(sid, sys_config);
            each(sys.neighs, function (k, n) {
                var pair = [sid, n].sort();
                neighs[pair.join('_')] = pair;
            });
            me[sid] = sys;
            if (sys_config.army) {
                sys.army = sys_config.army['units'];
            }
            if (sys.owner) {
                var pl = players[sys.owner];
                sys.stroke = pl.style.stroke;
                sys.fill = pl.style.fill;
            }
        });

        this.eventMarchOrderSelected = null;
        this.eventMarchHere = null;
        this.eventSupport = null;
    };

    Systems.prototype = new SmartHash(System, 'update');

    Systems.prototype.initPlanning = function (to_be_ordered) {
        this.eachFromList(function (sys) {
            sys.order = -1;
        }, to_be_ordered, true);
    };

    Systems.prototype.setupPlanningOnClick = function (to_be_ordered, order_click) {
        this.eachFromList(function (sys) {
            sys.order_click = order_click;
        }, to_be_ordered, true);
    };

    Systems.prototype.initMarches = function (march_routes) {
        var systems = this;
        this.eachFromObj(function (sys, march) {
            march.sys = sys;
            sys.order_click = function () {
                systems.eventMarchOrderSelected(march);
                sys.sent_units = [];
            };
        }, march_routes, true);
    };

    Systems.prototype.marchHereSystemsReset = function (sids) {
        var systems = this;
        each(sids, function (k, sid) {
            var sys = systems[sid];
            var m = sys.click;
            var u = sys.units;
            sys.click = null;
            sys.units = null;
            if (m || u) {
                sys.update();
            }
        });
    };

    Systems.prototype.setCouldBeMarchHere = function (sids) {
        var systems = this;
        this.eachFromList(function (sys) {
            sys.units = [];
            sys.click = function () {
                systems.eventMarchHere(sys);
                sys.update();
            };
        }, sids, true);
    };

    Systems.prototype.draw = function (paper, sys_drag_move, sys_drag_start) {
        this.each(function (sys) {
            sys.draw(paper);
            if (map.editable) {
                sys.anchor.drag(sys_drag_move, sys_drag_start);
            }
        }, true);
    };

    Systems.prototype.initSupports = function (supports) {
        var systems = this;
        systems.eachFromObj(function (sys, support) {
            sys.order.selected = false;
            sys.order.could_be_selected = true;
            sys.order_click = function () {
                sys.order.selected = !sys.order.selected;
                systems.eventSupport(sys, support);
                sys.update();
            };
        }, supports, true);
    };

})(jQuery, map);

