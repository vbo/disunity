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

    public function data($hid)
    {
        $current = $this->cur;
        $hids = array(
            $this->attacker => 1,
            $this->defender => 1
        );
        if (array_key_exists($hid, $hids)) {
            $hids = array($hid => 1);
        }
        $me = $this;
        $supports = array_map(function ($region) use ($me, $hid) {
            $orderComponent = array(
                'type' => 'order',
                'hid' => $hid,
                'order' => $region->order,
                'bonus' => $region->order->bonus
            );

            $defForce = array($orderComponent);
            $defForce = array_merge($defForce, Game_Army::defenceComponents($hid, $region->army->units));

            $attForce = array($orderComponent);
            $attForce = array_merge($attForce, Game_Army::attackComponents($hid, $region->army->units, $me->region->fort));
            return array(
                $me->attacker => $attForce,
                $me->defender => $defForce
            );
        }, $this->availSupports[$current]);

        return array(
            'cur_player' => $current,
            'bonuses' => $this->bonuses,
            'could_be_supported' => $hids,
            'supports' => $supports
        );
    }

    public function act($hid, $request)
    {
        if ($this->cur != $hid) {
            throw new Exception("Hack! $hid != {$this->cur}");
        }
        if (!isset($request->skip)) {
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
                if ($request->hid == $this->attacker) {
                    $armyBonus = Game_Army::attackComponents($hid, $units, $this->target->fort);
                } else {
                    $armyBonus = Game_Army::defenceComponents($hid, $units);
                }
                $this->bonuses[$request->hid][] = array(
                    'type' => 'order',
                    'hid' => $hid,
                    'order' => $region->order,
                    'bonus' => $region->order->bonus
                );
                foreach ($armyBonus as $bonus) {
                    $this->bonuses[$request->hid][] = $bonus;
                }
            }
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

