<?php

class Game_Node_Planning extends Game_Node
{
    public $turns = array();

    public function act($hid, $request)
    {
        $stars = $this->_game->tracks->stars($hid);
        $armies = $this->_game->map->armies($hid);
        $orders = $this->_game->orders->processOrders($hid, $request->orders, $stars, $armies);
        $this->_game->map->setOrders($orders);
        $this->turns[$hid] = 1;
        if (count($this->turns) < count($this->_game->players)) {
            return null;
        }
        return -1;
    }

    public function data($hid)
    {
        return array(
            'player_orders' => $this->_game->map->orders($hid),
            'available_orders' => $this->_game->orders->available(),
            'other_orders' => $this->_game->map->otherOrders($hid),
            'regions' => $this->_game->map->armyRegions($hid),
            'stars' => $this->_game->tracks->stars($hid)
        );
    }

    public function filterExportGame(&$exportGame, $hid) {
        $regions = &$exportGame['map']['regions'];
        foreach ($regions as $rid => $region) {
            $order = &$regions[$rid]['order'];
            if ($order && $regions[$rid]['owner'] != $hid) {
                // substitute order params with some garbage to hide it from hacker
                $order['type'] = Game_Order::Hidden;
                $order['star'] = 0;
                $order['bonus'] = 0;
                $order['icon'] = '';
            }
        }
    }
}

