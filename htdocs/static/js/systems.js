(function ($, map) {
    "use strict";

    var System = function (id, conf) {
        this.id = id;
        // holds a link to conf to update it in map.editable mode
        this.conf = conf;
        // holds a link to the current system owner
        this.owner = conf['owner'];
        // common system properties
        this.type = conf['type'];
        this.fort = conf['fort'];
        this.sources = conf['crowns'] || 0;
        this.supplies = conf['supplies'] || 0;
        this.homeland = conf['homeland'];
        this.title_text = conf['name'] + " [" + id + "]";
        this.neighs = conf['neighs'];
        // current order if any
        // we suppose that backend only tell us the orders
        // known by the current player
        this.order = conf['order'];
        this.units = null;
        this.enemy = conf['enemy'];
        this.enemy_house = conf['enemyHouse'];
        this.power = conf['power'];
        this.lord = conf['lord'];
        // event handlers
        this.order_click = null;
        this.click = null;
        // visual style
        var style = conf['style'];
        this.x = style['x'];
        this.y = style['y'];
        var orbs_default = [22];
        if (this.sources > 0 || this.supplies > 0) {
            // energy sources and supplies hold the first orb
            // so we need another one to deploy units or something
            orbs_default.push(32);
        }
        this.orbs = style['orbs'] || orbs_default;
        this.fill = style['fill'] || '#000';
        this.stroke = style['stroke'] || '#111';

        this.army = [];
        // define links to some DOM nodes
        // actual nodes will be created on draw()
        this.node = null;
        this.star = null;
        this.forts = null;
        this.planets = null;
        this.title = null;
        this.order_node = null;
        this.anchor = null;

        this._army = null;
        this._units = null;
        this._enemy = null;

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
        this._army = this.node.insertElement("g", {"class": "army"});
        this._units = this.node.insertElement("g", {"class": "units"});
        this._enemy = this.node.insertElement("g", {"class": "enemy"});
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
        this.anchor = this.node.appendChild($path.anchor(0, 0, max_orb));
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
        var me = this, max_orb = Math.max.apply(null, this.orbs);
        if (map.editable) {
            // currently only x/y props are editable
            this.conf.style.x = this.x;
            this.conf.style.y = this.y;
        }
        // we perform title reconstruction and recoloring
        // on every update for simplicity
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
        // update star coloring
        // todo: do the coloring using owner class
        this.star.setAttrValues({"stroke": this.stroke, "fill": this.fill});
        if (this.fort > 1) {
            this.forts.setAttrValues({"stroke": me.stroke, "fill": me.fill});
        }
        // next code recreates some DOM elements on every update
        // in map.editable mode updates performs very often so we can't use this code
        // because of performance degradation
        // todo: do it better ;)
        if (!map.editable) {
            // order node and its content visuals
            if (this.order_node) {
                this.order_node.remove();
                delete this.order_node;
            }
            if (this.order) {
                this.order_node = this.node.insertElement("g", {"class": "order_holder"});
                var bg = this.order_node.appendChild($path.order(0, 0, me)).setAttrValues({"class": "bg"});
                if (this.order.could_be_selected) {
                    bg.setAttrValues({'stroke': '#0033aa', 'fill': '#001122'});
                    if (this.order.selected) {
                        bg.setAttrValues({'fill': '#001144'});
                    }
                }
                // fixme: why order can be -1 and can be an object?
                if (this.order != -1 && this.order.icon) {
                    // we are here if the order contents need to be visible by the current player
                    // e.g. user just select some order for this system
                    // or it is an action phase where all orders already set
                    this.order_node.appendChild($path.order_glyph(0, 0, this.order.icon, me));
                    if (this.order.star) {
                        this.order_node.appendChild($path.order_star(0, 0, "star", me));
                    }
                    var str_bonus = number_format(this.order['bonus']);
                    var bonus = this.order_node.insertElement("text", {x: 33, y: -4, "fill": this.fill, "font-size": 9});
                    bonus.textContent = str_bonus;
                }
                var order_anchor = this.order_node.appendChild($path.order(0, 0, me).setAttrValues({"stroke-opacity": 0, "fill-opacity": 0}));
                order_anchor.onclick = function (evt) {
                    if (me.order_click) {
                        me.order_click(evt);
                    }
                };
            }

            this._army.removeAllChilds();
            this._enemy.removeAllChilds();
            this._units.removeAllChilds();

            var orb = this.orbs[this.orbs.length - 1],
                angle = 180 + 30;
            var sent_units = reach(this.sent_units, function (k, unit) {
                return unit.type;
            });
            this.army.forEach(function (unit_config) {
                var sent = pop_first(sent_units, function (k, u) {
                    return unit_config.type == u;
                });

                me._army.appendChild(
                    me.satellite(orb, angle, function (x, y) {
                        return $path.army(x, y, unit_config.type, me, sent);
                    })
                );
                angle += 50;
            });
            orb = max_orb + 16;
            angle = 90 + 30;
            if (this.units) {
                each(this.units, function (k, unit) {
                    me._units.appendChild(
                        me.satellite(orb, angle, function (x, y) {
                            return $path.enemy(x, y, unit.type, unit.homesys);
                        })
                    );
                    angle += 30;
                });
            }
            orb = max_orb + 16;
            angle = 90 + 30;
            if (this.enemy) {
                each(this.enemy, function (k, unit) {
                    me._enemy.appendChild(
                        me.satellite(orb, angle, function (x, y) {
                            return $path.enemy(x, y, unit.type, map.state.players[me.enemy_house].style);
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
