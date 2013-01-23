(function ($) {
    "use strict";

    var map = window.map = {
        paper: undefined,
        api: undefined,
        state: undefined,
        current_player: undefined,
        editable: false
    };
    map.parse = function (players, map_config) {
        var neighs = [];
        var systems = new map.Systems(players, map_config, neighs);
        var routes = new map.Routes(neighs, systems);
        return new map.World(players, systems, routes);
    };

    var World = map.World = function (players, systems, routes) {
        this.players = players;
        this.systems = systems;
        this.routes = routes;

        this.ctrl = null;
        this.routes_highlighted = [];
    };
    World.prototype.draw = function (paper) {
        var world = this;
        this.routes.draw();
        var system_drag_start = function () {
            this.system.ox = this.system.x;
            this.system.oy = this.system.y;
        };
        var system_drag_move = function (dx, dy) {
            this.system.x = this.system.ox + dx;
            this.system.y = this.system.oy + dy;
            this.system.update();
            world.routes.update();
        };
        world.systems.draw(paper, system_drag_move, system_drag_start);
    };

    World.prototype.setCtrl = function(phase, data) {
        var world = this;
        var router = {
            'planning': function (data) {
                $('.hud .phase_title span').text("Planning phase");
                return new map.PlanningCtrl(world.systems, data);
            },
            'march': function (data) {
                $('.hud .phase_title span').text("Action phase :: Marches");
                if (data['cur_player'] == map.current_player['house']) {
                    console.log("you are current");
                    return new map.MarchCtrl(world.systems, world.routes, map.state['map']['orders'], data);
                } else {
                    console.log(data['cur_player'] + ' is current');
                }
                return null;
            },
            'power': PowerCtrl
        };
        console.log(phase + " phase");
        this.ctrl = router[phase](data);
        if (this.ctrl) {
            this.ctrl.setup();
        }
    };

    var PowerCtrl = function (world, data) {
        $('.hud .phase_title span').text("Power phase");
        console.log('not implemented');
    };

})(jQuery);

