(function ($) {
    "use strict";

    var hud = window.hud = {
        cur_units: null,
        unit_systems: null,
        routes: null,
        homesys: null
    };

    var world;
    var players;
    var current_player;

    hud.init = function (_world, _players, _current_player) {
        world = _world;
        players = _players;
        current_player = _current_player;
        console.log(current_player);
        console.log($('.hud .brand'));
        var title = 'You are ' + current_player.name;
        $('.hud .brand').css("color", current_player.style.stroke).attr('title', title).attr('name', title);
        each(current_player['resources'], function (k, v) {
            $('.hud .resource.' + k).find('span').text(v);
        });
    };

    hud.setupTracks = function (tracks) {
        each(tracks, function (t, track) {
            var cnt = $('.hud .tracks .' + t + ' ul');
            cnt.find('li').remove();
            track.forEach(function (hid) {
                var pl = players[hid];
                var pos = $('<li></li>');
                pos.css("background-color", pl.style.stroke);
                pos.appendTo(cnt);
            });
        });
    };

})(jQuery);

