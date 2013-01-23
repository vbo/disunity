<?php

class Game_Node_Raid extends Game_Node
{
    public $cur;

    protected function _init()
    {
        return $this->_next();
    }

    private function _next()
    {
        if (!$this->_findNext()) {
            return -1;
        }
        return null;
    }

    private function _findNext()
    {
        $game = $this->_game;
        return $game->tracks->trackOrder(Game_Track::Throne, $this->cur, function($hid, &$cur) use ($game) {
            if (!$game->map->orders($hid, Game_Order::Raid)) {
                return false;
            }
            $cur = $hid;
            return true;
        });
    }

    public function act($hid, $request)
    {
        if ($this->cur != $hid) {
            throw new Exception("Hack!");
        }

        $source = $request->source;

        $order = $this->_game->map->order($source);
        if ($order->hid != $hid || !$order->check(Game_Order::Raid)) {
            throw new Exception("Wrong source region: `$source`");
        }

        if (isset($request->skip)) {
            $this->_game->map->unsetOrder($source);
        } else {
            $this->_raid($order, $source, $request->target);
        }

        return $this->_next();
    }

    private function _raid($order, $source, $target)
    {
        $targetOrder = $this->_game->map->order($target);
        if (!Game_Order::checkRaid($order, $targetOrder)) {
            throw new Exception("Wrong target region: order");
        }
        if (!$this->_game->map->checkRaid($source, $target)) {
            throw new Exception("Wrong target region: region");
        }
        if ($targetOrder->check(Game_Order::Power)) {
            $this->_raidPower($order->hid, $targetOrder->hid);
        }
        $this->_game->map->unsetOrder($source);
        $this->_game->map->unsetOrder($target);
    }

    // todo: raid region with power order
    private function _raidPower($hid, $targetHid)
    {
    }
}

