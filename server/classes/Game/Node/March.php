<?php

class Game_Node_MarchException extends Exception {
    const NO_FORCE = 1;
    const TOO_MANY_FIGHTS = 2;
    const NOT_MARCH = 3;
    const PORT_ATTACK = 5;
    const NOT_NEIGH_REGION = 6;
    const ARMY_HID = 9;
}

class Game_Node_March extends Game_Node
{
    public $cur;

    protected function _init()
    {
        return $this->_next();
    }

    public function data($hid)
    {
        $current = $this->cur;
        return array(
            'cur_player' => $current,
            'routes' => $this->_game->map->availableMarchRoutes($current),
        );
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
            if (!$game->map->orders($hid, Game_Order::March)) {
                return false;
            }
            $cur = $hid;
            return true;
        });
    }

    public function act($hid, $request)
    {
        if ($this->cur != $hid) {
            throw new Exception("Hack! $hid != {$this->cur}");
        }
        $source = $request->source;

        $order = $this->_game->map->order($source);
        if (!$order || $order->hid != $hid || !$order->check(Game_Order::March)) {
            throw new Game_Node_MarchException("Wrong source region: `$source`", Game_Node_MarchException::NOT_MARCH);
        }

        if (@$request->power) {
            $player = $this->_game->players[$hid];
            $player->subPower();
            $this->_game->map->power($source);
        }

        if (!isset($request->skip)) {
            $fight = $this->_march($order, $source, $request->marches);
            if ($fight) {
                return $fight;
            }
        }
        $this->_game->map->unsetOrder($source);
        return $this->_next();
    }

    private function _march($order, $source, $marches)
    {
        $fight = null;
        $from = $this->_game->map->r($source);
        foreach ($marches as $rid => $units) {
            $to = $this->_game->map->r($rid);
            if (!$units) {
                throw new Game_Node_MarchException("No force", Game_Node_MarchException::NO_FORCE);
            }
            $marchedArmy = $from->subUnits($units);
            $this->_game->map->assertMarchPossible($order->hid, $source, $rid);
            if ($to->army && $to->owner != $order->hid) {
                if ($fight) {
                    throw new Game_Node_MarchException("There could be only one fight", Game_Node_MarchException::TOO_MANY_FIGHTS);
                }
                $fight = new Game_Node_Fight($order->hid, $marchedArmy, $from, $to);
                continue;
            }
            $to->addUnits($marchedArmy);
        }
        return $fight;
    }

    public function childFinished($node)
    {
        if ($node instanceof Game_Node_Fight) {
            if ($node->winner == $this->cur) {
                $node->target->addUnits($node->units);
            } else {
                $node->source->addUnits($node->units);
            }
        }
        return $this->_next();
    }
}

