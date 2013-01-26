<?php

class Game_Node_SupportException extends Exception {}
class Game_Node_SupportExceptionWrongHouse extends Game_Node_SupportException {}
class Game_Node_SupportExceptionWrongRegion extends Game_Node_SupportException {}

class Game_Node_Support extends Game_Node
{
    public $bonuses;

    public $attacker;
    public $defender;
    public $region;

    public $supportRegions;
    public $cur;
    public $availSupports;

    public function __construct($attacker, $defender, $region, $bonuses)
    {
        $this->bonuses = $bonuses;
        $this->attacker = $attacker;
        $this->defender = $defender;
        $this->region = $region;
    }

    protected function _init()
    {
        $this->availSupports = $this->_game->map->supports($this->region);
        return $this->_next();
    }

    public function act($hid, $request)
    {
        if ($this->cur != $hid) {
            throw new Exception("Hack! $hid != {$this->cur}");
        }
        if (!in_array($request->hid, array($this->attacker, $this->defender))) {
            throw new Game_Node_SupportExceptionWrongHouse("House {$request->hid} couldn't be supported");
        }
        if ($hid == $this->attacker || $hid == $this->defender) {
            if ($request->hid != $hid) {
                throw new Game_Node_SupportExceptionWrongHouse("House $hid can support only itself");
            }
        }
        $availSupports = $this->availSupports[$hid];
        foreach ($request->rids as $rid) {
            if (!array_key_exists($rid, $availSupports)) {
                throw new Game_Node_SupportExceptionWrongRegion("$rid is a wrong support region");
            }
            $region = $availSupports[$rid];
            $units = $region->army->units;
            $armyBonus = 0;
            if ($request->hid == $this->attacker) {
                $armyBonus = Game_Army::attackForce($units);
            } else {
                $armyBonus = Game_Army::defenceForce($units);
            }
            $this->bonuses[$request->hid]['support'] += $region->order->bonus + $armyBonus;
        }
        unset($this->availSupports[$hid]);
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
        $me = $this;
        return $this->_game->tracks->trackOrder(Game_Track::Throne, $this->cur, function($hid, &$cur) use ($me) {
            if (!isset($me->availSupports[$hid])) {
                return false;
            }
            $cur = $hid;
            return true;
        });
    }
}

