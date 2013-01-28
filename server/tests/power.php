<?php
require_once(dirname(__FILE__) . '/../bootstrap.php');
$configFile = dirname(__FILE__) . '/../../config/game.json';

$file = '/tmp/1.sav';
$storage = new Game_Storage($file);
$storage->clear();

$playersNumber = 3;
$game = new Game($storage, $playersNumber, $configFile);
$game->commit();

$turn = function ($hid, $request, $callback=null, $excallback=null) use ($storage) {
    $game = $storage->load();
    try {
        ob_start();
        $game->turn($hid, $request);
        $game->commit();
        ob_end_clean();
    } catch (Exception $e) {
        if ($excallback) {
            $r =  $excallback($game, $e)
                ? '.'
                : 'E';
            print $r;
            if ($r == 'E') {
                throw $e;
            }
            return;
        }
        throw $e;
    }
    if ($excallback) {
        print 'E';
        return;
    }
    if ($callback) {
        print $callback($game)
            ? '.'
            : 'E';
    } else {
        print '.';
    }
};

$requests = array(

    // ----------------------------------------------------------- PLANNING

    array(House::Baratheon, array(
        'orders' => array(
            56 => Game_Order::DefenseBasic,
            59 => Game_Order::PowerStar,
            27 => Game_Order::PowerBasic,
        ))),

    array(House::Lannister, array(
        'orders' => array(
            19 => Game_Order::PowerStar,
            51 => Game_Order::PowerBasic,
            21 => Game_Order::PowerBasic,
        ))),

    array(House::Stark, array(
        'orders' => array(
            3 => Game_Order::PowerBasic,
            4 => Game_Order::PowerStar,
            47 => Game_Order::PowerBasic
        )), function($game) {
            $node = $game->stack->end();
            return $node instanceof Game_Node_Power;
        }
    ),

    // ----------------------------------------------------------- POWER

    array(House::Baratheon, array(
            'source' => 56,
        ), null, function($game, $e) {
            return $e instanceof Game_Node_PowerExceptionWrongRegion;
        }
    ),

    array(House::Baratheon, array(
            'source' => 27,
        ), function($game) {
            return $game->players[House::Baratheon]->resources['power'] == 7;
        }
    ),

    array(House::Lannister, array(
            'source' => 21,
        ), function($game) {
            return $game->players[House::Lannister]->resources['power'] == 7;
        }
    ),

    array(House::Stark, array(
            'source' => 4,
            'construct' => array(5 => array(1))
        ), null, function($game, $e) {
            return $e instanceof Game_RegionExceptionConstructionUnit;
        }
    ),

    array(House::Stark, array(
            'source' => 4,
            'construct' => array(4 => array(3))
        ), null, function($game, $e) {
            return $e instanceof Game_RegionExceptionConstructionUnit;
        }
    ),

    array(House::Stark, array(
            'source' => 4,
            'construct' => array(4 => array(2))
        ), null, function($game, $e) {
            return $e instanceof Game_MapExceptionConstructionLimit;
        }
    ),

    array(House::Stark, array(
            'source' => 4,
            'construct' => array(4 => array(1, 1))
        ), null, function($game, $e) {
            return $e instanceof Game_MapExceptionConstructionLimit;
        }
    ),

    array(House::Stark, array(
            'source' => 4,
            'construct' => array(10 => array(1))
        ), null, function($game, $e) {
            return $e instanceof Game_MapExceptionWrongConstructionRegion;
        }
    ),

    array(House::Stark, array(
            'source' => 4,
            'construct' => array(4 => array(1))
        ), function($game) {
            return $game->players[House::Stark]->resources['power'] == 5
                    && $game->map->r(4)->army->units === array(1, 1);
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'construct' => array(45 => array(3, 3, 3))
        ), null, function($game, $e) {
            return $e instanceof Game_MapExceptionConstructionLimit;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'construct' => array(59 => array(2, 1))
        ), null, function($game, $e) {
            return $e instanceof Game_MapExceptionConstructionLimit;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'construct' => array(59 => array(1, 2))
        ), null, function($game, $e) {
            return $e instanceof Game_MapExceptionConstructionLimit;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'construct' => array(59 => array(1, 1)),
            'upgrade' => array(array(1, 2))
        ), null, function($game, $e) {
            return $e instanceof Game_MapExceptionConstructionLimit;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'upgrade' => array(array(1, 2), array(2, 2)),
        ), null, function($game, $e) {
            return $e instanceof Game_RegionExceptionUpgradeUnit;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'construct' => array(59 => array(1)),
            'upgrade' => array(array(1, 3)),
        ), null, function($game, $e) {
            return $e instanceof Game_RegionExceptionUpgradeUnitTo;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'upgrade' => array(array(1, 2), array(1, 2)),
        ), null, function($game, $e) {
            return $e instanceof Game_ArmyException && $e->getCode() == Game_ArmyException::LACK_OF_UNITS;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'construct' => array(56 => array(3), 59 => array(3))
        ), null, function($game, $e) {
            return $e instanceof Game_RegionExceptionConstructionUnit;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'construct' => array(56 => array(3), 45 => array(3, 3))
        ), null, function($game, $e) {
            return $e instanceof Game_MapExceptionConstructionLimit;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'construct' => array(23 => array(1))
        ), null, function($game, $e) {
            return $e instanceof Game_MapExceptionWrongConstructionRegion;
        }
    ),

    array(House::Baratheon, array(
            'source' => 59,
            'construct' => array(45 => array(3)),
            'upgrade' => array(array(1, 4))
        ), function($game) {
            return $game->players[House::Baratheon]->resources['power'] == 7
                    && !array_diff($game->map->r(59)->army->units, array(4, 2))
                    && !array_diff($game->map->r(45)->army->units, array(3));
        }
    ),

    array(House::Lannister, array(
            'source' => 51,
        ), function($game) {
            return $game->players[House::Lannister]->resources['power'] == 7;
        }
    ),

);

foreach ($requests as $i => $request) {
    //echo $i;
    call_user_func_array($turn, $request);
    //print "\n";
}
print "\n";


