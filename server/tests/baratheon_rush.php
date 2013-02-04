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
            56 => Game_Order::MarchBasic,
            59 => Game_Order::MarchStar,
            27 => Game_Order::MarchWeak,
        ))
    ),

    array(House::Lannister, array(
        'orders' => array(
            19 => Game_Order::MarchStar,
            51 => Game_Order::MarchBasic,
            21 => Game_Order::MarchWeak,
        ))
    ),

    array(House::Stark, array(
        'orders' => array(
            3 => Game_Order::MarchBasic,
            4 => Game_Order::DefenseBasic,
            47 => Game_Order::SupportBasic
        ))
    ),

    // -------------------------------------------------- MARCH
    array(House::Baratheon, array(
        'source' => 56,
        'marches' => array(
            49 => array("robot"),
        ),
    )),
    array(House::Lannister, array(
        'source' => 19,
        'marches' => array(
            18 => array("fighter", "cruiser")
        ))
    ),
    array(House::Stark, array(
        'source' => 3,
        'marches' => array(
            4 => array("fighter")
        ))
    ),
    array(House::Baratheon, array(
            'source' => 27,
            'marches' => array(
                59 => array("fighter"),
            )
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
            10 => array("fighter", "cruiser", "fighter"),
        )),
    ),
    array(House::Lannister, array(
        'source' => 51,
        'marches' => array(
            50 => array("robot")
        ))
    ),
    // ----------------------------------------------------------- PLANNING

    array(House::Baratheon, array(
        'orders' => array(
            10 => Game_Order::MarchStar,
            49 => Game_Order::MarchWeak,
            56 => Game_Order::SupportBasic,
        ))
    ),

    array(House::Lannister, array(
        'orders' => array(
            18 => Game_Order::MarchStar,
            50 => Game_Order::SupportBasic,
            22 => Game_Order::MarchWeak,
        ))
    ),

    array(House::Stark, array(
        'orders' => array(
            3 => Game_Order::SupportBasic,
            4 => Game_Order::DefenseBasic,
            47 => Game_Order::SupportStar
        ))
    ),

    // -------------------------------------------------- MARCH
    array(House::Baratheon, array(
        'source' => 49,
        'skip' => 1
    )),
    array(House::Lannister, array(
        'source' => 18,
        'marches' => array(
            11 => array("fighter", "cruiser")
        ),
        'power' => 1
    )),
    array(House::Baratheon, array(
        'source' => 10,
        'marches' => array(
            '3' => array("fighter", "cruiser", "fighter")
        )
    )),
    // support
    array(House::Stark, array(
        'skip' => 1
    )),
    // retreat
    array(House::Stark, array(
        'rid' => 4
    )),
    array(House::Lannister, array(
        'source' => 22,
        'marches' => array(
            23 => array("fighter")
        ),
        'power' => 1
    )),
    //
    // ----------------------------------------------------------- PLANNING

    array(House::Baratheon, array(
        'orders' => array(
            3 => Game_Order::DefenseStar,
            49 => Game_Order::SupportBasic,
            56 => Game_Order::SupportBasic,
        ))
    ),

    array(House::Lannister, array(
        'orders' => array(
            11 => Game_Order::MarchBasic,
            50 => Game_Order::SupportBasic,
            23 => Game_Order::DefenseStar,
        ))
    ),

    array(House::Stark, array(
        'orders' => array(
            4 => Game_Order::DefenseBasic,
            47 => Game_Order::SupportStar
        ))
    ),

    // -------------------------------------------------- MARCH
    array(House::Lannister, array(
        'source' => 11,
        'marches' => array(
            10 => array("cruiser", "fighter")
        )
    )),
    // ----------------------------------------------------------- PLANNING

    array(House::Baratheon, array(
        'orders' => array(
            3 => Game_Order::MarchStar,
            49 => Game_Order::SupportBasic,
            56 => Game_Order::SupportBasic,
        ))
    ),

    array(House::Lannister, array(
        'orders' => array(
            10 => Game_Order::SupportStar,
            50 => Game_Order::SupportBasic,
            23 => Game_Order::DefenseStar,
        ))
    ),

    array(House::Stark, array(
        'orders' => array(
            4 => Game_Order::DefenseBasic,
            47 => Game_Order::SupportStar
        ))
    ),

    // -------------------------------------------------- MARCH
    array(House::Baratheon, array(
        'source' => 3,
        'marches' => array(
            4 => array("cruiser", "fighter", "fighter")
        )
    )),
    array(House::Baratheon, array(
        'hid' => House::Baratheon,
        'rids' => array(49)
    )),
    array(House::Lannister, array(
        'hid' => House::Baratheon,
        'rids' => array(10)
    )),
    array(House::Stark, array(
        'hid' => House::Stark,
        'rids' => array(47)
    )),
    array(House::Stark, array(
        'rid' => 6,
    )),
    // ----------------------------------------------------------- PLANNING

    array(House::Baratheon, array(
        'orders' => array(
            4 => Game_Order::DefenseBasic,
            49 => Game_Order::MarchStar,
            56 => Game_Order::SupportBasic,
        ))
    ),

    array(House::Lannister, array(
        'orders' => array(
            10 => Game_Order::SupportStar,
            50 => Game_Order::SupportBasic,
            23 => Game_Order::DefenseStar,
        ))
    ),

    array(House::Stark, array(
        'orders' => array(
            6 => Game_Order::DefenseBasic,
            47 => Game_Order::SupportStar
        ))
    ),
);


foreach ($requests as $i => $request) {
    //echo $i;
    call_user_func_array($turn, $request);
    //print "\n";
}
print "\n";

