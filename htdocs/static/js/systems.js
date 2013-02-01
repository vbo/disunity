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
        this.power = conf.power;
        this.lord = conf.lord;
        this.homeland = conf.homeland;
        this.sources = conf['crowns'] || 0;
        this.supplies = conf.supplies || 0;
        this.conf = conf;
        this.neighs = conf.neighs;
        this.type = conf.type;
        this.title_text = conf.name + "(" + id + ")";
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

        this.node = undefined;
        this.star = undefined;
        this.forts = undefined;
        this.planets = undefined;
        this.title = undefined;
        this.order_node = undefined;

        this.anchor = undefined;
        this._army = [];
        this._units = [];
        this._enemy = [];
        this._order = undefined;
        this._order_anchor = undefined;
        this._order_glyph = undefined;
        this._order_star = undefined;
        this._order_bonus = undefined;

        this.orb_stroke = '#005571';
    };

    System.prototype.draw = function () {
        // we are here when we need to initially draw
        // the system on the canvas
        var me = this;
        if (this.fort > 1) {
            // we need to enlarge star orbs
            var fort = this.fort;
            this.orbs = this.orbs.map(function (v) { return v + fort * 3 });
        }
        var max_orb = Math.max.apply(null, this.orbs);
        // root system node
        this.node = map.paper.insertElement("g", {
            "class": "system", "id": this.id,
            // todo: transformation lib
            "transform": "translate(" + this.x + "," + this.y + ")"
        });
        // black circle bg (to add some padding for routes)
        this.node.appendChild($path.bg(0, 0, max_orb));
        // forts node holds visuals indicating most important - habitable systems
        // todo: do it better ;)
        if (this.fort > 1) {
            this.forts = this.node.insertElement("g", {"class": "forts"});
            this.forts.appendChild($path.fort_orb_2(0, 0, this));
            if (this.fort > 2) {
                this.forts.appendChild($path.fort_orb_3(0, 0, this));
            }
        }
        // star and orbs
        this.star = this.node.appendChild($path.star(0, 0, this).setAttrValues({"class": "star"}));
        this.orbs.forEach(function (orb) {
            var o = me.node.appendChild($path.orb(0, 0, orb, me));
        });
        // planets indicate systems with resources
        this.planets = this.node.insertElement("g", {"class": "planets"});
        var orb = this.orbs[0]; // min orb
        var angle = 90 + 30;
        var planets = function (cnt, color) {
            for (var i = 0; i < cnt; i++) {
                me.planets.appendChild(me.satellite(orb, angle, function (x, y) {
                    return $path.satellite(x, y, color, me);
                }));
                angle += 27;
            }
        };
        planets(this.sources, "yellow");
        planets(this.supplies, "green");
        // system title (it's really useful when you try to perform some negotiations with other players)
        this.title = this.node.insertElement("g", {"class": "title", "font-size": 12});
        this.title.text = this.title.insertElement("text", {x: 0, y: max_orb + 11 + 5});
        this.title.text.textContent = this.title_text;
        // anchor is a transparent overlay for click handling
        this.anchor = $path.anchor(0, 0, max_orb);
        this.anchor.system = this;
        this.anchor.onclick = function (evt) {
            if (me.click) {
                me.click(evt);
            }
        };
    };

    System.prototype.satellite = function (orb, angle, draw) {
        var rad = Raphael.rad(angle);
        return draw(Math.round(orb * Math.cos(rad)), -Math.round(orb * Math.sin(rad)));
    };

    System.prototype.update = function () {
        // additionally updates config
        var me = this, max_orb = Math.max.apply(null, this.orbs);
        if (map.editable) {
            // currently only x/y props are editable
            this.conf.style.x = this.x;
            this.conf.style.y = this.y;
        }
        this.star.setAttrValues({"stroke": this.stroke, "fill": this.fill});
        this.title.text.setAttrValues({"fill": '#005571', "x": 0});
        if (this.title.force) {
            this.title.frame.remove();
            this.title.force.remove();
        }
        if (this.power) {
            this.title.text.setAttribute("fill", this.fill);
        }
        var force = this.homeland || this.lord;
        if (force) {
            var color = this.lord ? '#005571' : this.fill;
            this.title.text.setAttribute('x', 10);
            var force_text = this.title.force = this.title.insertElement("text", {
                x: -this.title.getBBox().width / 2,
                y: max_orb + 11 + 5
            }).setAttrValues({'fill': color, 'font-size': 12});
            force_text.textContent = force;
            this.title.frame = this.title.appendChild($path.frame(
                -this.title.text.getBBox().width / 2,
                max_orb + 11,
                this.title.text.getBBox().width + 10,
                10,
                5,
                this.title.text.getBBox().height,
                color
            ));
        }
        if (this.fort > 1) {
            // todo: do the coloring using owner class
            this.forts.setAttrValues({"stroke": me.stroke, "fill": me.fill});
        }
        if (this.order_node) {
            this.order_node.remove();
            delete this.order_node;
        }
        if (this.order) {
            this.order_node = this.node.insertElement("g", {"class": "order_holder"});
            this.order_node.bg = this.order_node.appendChild($path.order(0, 0, me)).setAttrValues({"class": "bg"});
            if (this.order.could_be_selected) {
                this.order_node.bg.setAttrValues({'stroke': '#0033aa', 'fill': '#001122'});
                if (this.order.selected) {
                    this.order_node.bg.setAttrValues({'fill': '#001144'});
                }
            }
            if (this.order != -1) {
                if (this.order.icon) {
                    this.order_node.glyph = this.order_node.appendChild($path.order_glyph(0, 0, this.order.icon, me));
                    if (this.order.star) {
                        this.order_node.star = this.order_node.appendChild($path.order_star(0, 0, "star", me));
                    }
                    var str_bonus = number_format(this.order['bonus']);
                    this.order_node.bonus = this.order_node.insertElement("text", {x: 33, y: -4, "fill": this.fill, "font-size": 9});
                    this.order_node.bonus.textContent = str_bonus;
                }
            }
            this.order_node.anchor = this.order_node.appendChild($path.order(0, 0, me).setAttrValues({
                "stroke-opacity": 0, "fill-opacity": 0
            }));
            this.order_node.anchor.onclick = function (evt) {
                if (me.order_click) {
                    me.order_click(evt);
                }
            };
        }
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
        this.eventRetreat = null;
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

    Systems.prototype.initRetreat = function (homesys, sids) {
        var systems = this;
        this.eachFromList(function (sys) {
            sys.click = function () {
                var home = systems[homesys];
                if (!home.sent_units) {
                    home.sent_units = reach(home.army, function (k, unit_type) {
                        return {'type': unit_type, 'homesys': home};
                    });
                }
                sys.units = home.sent_units;
                sys.update();
                home.update();
                systems.eventRetreat(sys);
            };
        }, sids);
    };

    Systems.prototype.handleRetreatCancelled = function (sys) {
        sys.units = null;
        sys.update();
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

