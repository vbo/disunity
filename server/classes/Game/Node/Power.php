<?php

class Game_Node_PowerException extends Exception {}
class Game_Node_PowerExceptionWrongRegion extends Game_Node_PowerException {}

class Game_Node_Power extends Game_Node
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
            if (!$game->map->orders($hid, Game_Order::Power)) {
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

        $source = $this->_game->map->r($request->source);
        $order = $source->order;

        if ($order->hid != $hid || !$order->check(Game_Order::Power)) {
            throw new Game_Node_PowerExceptionWrongRegion("Wrong source region: `{$source->id}`");
        }

        $construct = @$request->construct ?: array();
        $upgrade = @$request->upgrade ?: array();
        if ($order->star && $construct || $upgrade) {
            $this->_game->map->construct($source, $construct, $upgrade);
        } else {
            $bonus = $source->type == Game_Region::Land ? $source->crowns + 1 : 0;
            if ($bonus) {
                $this->_game->players[$hid]->addPower($bonus);
            }
        }
        $source->unsetOrder();

        return $this->_next();
    }
}

