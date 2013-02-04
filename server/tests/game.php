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
        //var_dump(end($game->state->stack));
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
            56 => Game_Order::MarchBasic,
            59 => Game_Order::MarchStar,
            27 => Game_Order::MarchWeak,
        )), function($game) {
            $node = $game->stack->end();
            return $node instanceof Game_Node_Planning
                && count($node->turns) == 1
                && isset($game->map->r(56)->order)
                && isset($game->map->r(59)->order)
                && isset($game->map->r(27)->order);
        }
    ),

    array(House::Lannister, array(
        'orders' => array(
            19 => Game_Order::DefenseStar,
            51 => Game_Order::DefenseBasic,
            21 => Game_Order::DefenseStar,
        )), null, function($game, $e) {
            return $e instanceof Game_OrdersException
                && $e->getCode() == Game_OrdersException::NO_ORDER;
        }
    ),

    array(House::Lannister, array(
        'orders' => array(
            19 => Game_Order::MarchStar,
            51 => Game_Order::RaidStar,
            21 => Game_Order::MarchWeak,
        )), function($game) {
            $node = $game->stack->end();
            return $node instanceof Game_Node_Planning
                && count($node->turns) == 2
                && isset($game->map->r(19)->order)
                && isset($game->map->r(51)->order)
                && isset($game->map->r(21)->order);
        }
    ),

    array(House::Stark, array(
        'orders' => array(
            3 => Game_Order::DefenseStar,
            4 => Game_Order::SupportStar,
            47 => Game_Order::RaidStar
        )), null, function($game, $e) {
            return $e instanceof Game_OrdersException
                && $e->getCode() == Game_OrdersException::LACK_OF_STARS;
        }
    ),

    array(House::Stark, array(
        'orders' => array(
            3 => Game_Order::MarchBasic,
            4 => Game_Order::DefenseBasic,
            47 => Game_Order::SupportStar
        )), function($game) {
            $node = $game->stack->end();
            return $node instanceof Game_Node_Raid
                && isset($game->map->r(3)->order)
                && isset($game->map->r(4)->order)
                && isset($game->map->r(47)->order);
        }
    ),

    // -------------------------------------------------- RAID

    array(House::Lannister, array(
            'source' => 51,
            'skip' => true,
        ), function($game) {
            $node = $game->stack->end();
            return $node instanceof Game_Node_March
                && !isset($game->map->orders[House::Lannister][51]);
        }
    ),

    // -------------------------------------------------- MARCH
    array(House::Baratheon, array(
        'source' => 56,
        'marches' => array(
            23 => array("robot")
        )), null, function($game, $e) {
            return $e instanceof Game_MapException
                && $e->getCode() == Game_MapException::WRONG_TARGET_REGION;
        }
    ),

    array(House::Baratheon, array(
        'source' => 56,
        'marches' => array(
            46 => array("robot")
        )), null, function($game, $e) {
            return $e instanceof Game_MapException
                && $e->getCode() == Game_MapException::NO_WAY;
        }
    ),

    array(House::Baratheon, array(
        'source' => 57,
        'marches' => array(
            46 => array("robot")
        )), null, function($game, $e) {
            return $e instanceof Game_Node_MarchException
                && $e->getCode() == Game_Node_MarchException::NOT_MARCH;
        }
    ),

    array(House::Baratheon, array(
        'source' => 56,
        'marches' => array(
            49 => array("robot", "robot", "robot")
        )), null, function($game, $e) {
            return $e instanceof Game_ArmyException
                && $e->getCode() == Game_ArmyException::LACK_OF_UNITS;
        }
    ),

    array(House::Baratheon, array(
        'source' => 56,
        'marches' => array(
            49 => array("fighter")
        )), null, function($game, $e) {
            return $e instanceof Game_ArmyException
                && $e->getCode() == Game_ArmyException::LACK_OF_UNITS;
        }
    ),

    array(House::Baratheon, array(
        'source' => 59,
        'marches' => array(
            26 => array("fighter")
        )), null, function($game, $e) {
            return $e instanceof Game_MapException
                && $e->getCode() == Game_MapException::NO_WAY;
        }
    ),

    array(House::Baratheon, array(
            'source' => 56,
            'marches' => array(
                49 => array("fighter")
            ),
            'power' => true
        ), null, function($game, $e) {
            return $e instanceof Game_MapException
                && $e->getCode() == Game_MapException::WRONG_POWER_REGION;
        }
    ),
    array(House::Baratheon, array(
        'source' => 56,
        'marches' => array(
            49 => array("robot"),
        ),
    )),
    array(House::Lannister, array(
        'source' => 19,
        'marches' => array(
            18 => array("cruiser")
        ))
    ),
    array(House::Stark, array(
        'source' => 3,
        'marches' => array(
            10 => array("cruiser"),
            2 => array("fighter")
        ))
    ),
    array(House::Baratheon, array(
            'source' => 27,
            'marches' => array(
                59 => array("fighter"),
            ),
            'power' => 1
        ),
    ),
    array(House::Lannister, array(
        'source' => 21,
        'marches' => array(
            22 => array("fighter")
        ))
    ),
    array(House::Baratheon, array(
            'source' => 59,
            'marches' => array(
                4 => array("fighter", "cruiser", "fighter"),
            ), 'power' => true
        ), null, function ($game, $e) {
            return $e instanceof Game_MapException
                && $e->getCode() == Game_MapException::ALREADY_POWERED;
        }
    ),
    array(House::Baratheon, array(
        'source' => 59,
        'marches' => array(
            4 => array("fighter", "cruiser", "fighter"),
        )), function ($game) {
            $node = $game->stack->end();
            $bonuses = $node->bonuses;
            return $node instanceof Game_Node_Support
                && $node->attacker = House::Baratheon
                && $node->defender = House::Stark
                && $node->region->id = 4;
        }
    ),
    array(House::Stark, array(
            'hid' => House::Baratheon,
            'rids' => array(47)
        ), null, function ($game, $e) {
            return $e instanceof Game_Node_SupportExceptionWrongHouse;
        }
    ),
    array(House::Stark, array(
            'hid' => House::Lannister,
            'rids' => array(47)
        ), null, function ($game, $e) {
            return $e instanceof Game_Node_SupportExceptionWrongHouse;
        }
    ),
    array(House::Stark, array(
            'hid' => House::Stark,
            'rids' => array(46)
        ), null, function ($game, $e) {
            return $e instanceof Game_Node_SupportExceptionWrongRegion;
        }
    ),
    array(House::Baratheon, array(
            'hid' => House::Stark,
            'rids' => array(46)
        ), null, function ($game, $e) {
            return true;
        }
    ),
    array(House::Stark, array(
            'hid' => House::Stark,
            'rids' => array(47)
        ), function ($game) {
            $node = $game->stack->end();
            $routes = array_keys($node->routes);
            $expected = array(1, 2, 3, 10, 6);
            return $node instanceof Game_Node_Retreat
                && $node->hid == House::Stark
                && count(array_intersect($routes, $expected)) == count($expected);
        }
    ),
    array(House::Stark, array(
            'rid' => 3
        ), function ($game) {
            $node = $game->stack->end();
            return $node instanceof Game_Node_Westeros;
        }
    ),
);

foreach ($requests as $i => $request) {
    //echo $i;
    call_user_func_array($turn, $request);
    //print "\n";
}
print "\n";

