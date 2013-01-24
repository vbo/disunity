<?php

class Game_MapException extends Exception
{
    const WRONG_TARGET_REGION = 1;
    const NO_WAY = 2;
    const ALREADY_POWERED = 3;
    const WRONG_POWER_REGION = 4;
}

class Game_Map
{
    public $regions = array();

    public function __construct($regions, $homeRegions, $armies, $lords)
    {
        foreach ($regions as $rid => $region) {
            $this->regions[$rid] = new Game_Region($rid, $region, @$armies[$rid], @$lords[$rid], @$homeRegions[$rid]);
        }
        $this->_checkConsistency();
    }

    public function setOrders($orders)
    {
        foreach ($orders as $rid => $order) {
            $this->r($rid)->setOrder($order);
        }
    }

    public function unsetOrder($rid)
    {
        $this->r($rid)->unsetOrder();
    }

    public function order($rid)
    {
        return $this->r($rid)->order;
    }

    public function orders($hid=null, $type=null)
    {
        return array_map(function ($region) {
                return $region->order;
            }, array_filter($this->regions, function ($region) use ($hid, $type) {
            $order = $region->order;
            if (!$order || $type && $order->type != $type) {
                return false;
            }
            return is_null($hid) || $order->hid == $hid;
        }));
    }

    public function otherOrders($hid)
    {
        return array_keys(array_filter($this->regions, function($region) use ($hid) {
            $order = $region->order;
            if (!$order) {
                return false;
            }
            return $order->hid != $hid;
        }));
    }

    public function power($rid)
    {
        if (!$this->check($rid, Game_Region::Land)) {
            throw new Game_MapException("Couldn't power this region: `$rid`", Game_MapException::WRONG_POWER_REGION);
        }
        if ($this->r($rid)->power) {
            throw new Game_MapException("Try to power already powered region: `$rid`", Game_MapException::ALREADY_POWERED);
        }
        $this->r($rid)->power = 1;
    }

    public function army($rid)
    {
        return $this->r($rid)->army;
    }

    public function armies($hid)
    {
        return array_filter($this->regions, function($region) use ($hid) {
            return $region->army && $region->army->hid == $hid;
        });
    }

    public function armyRegions($hid)
    {
        return array_keys($this->armies($hid));
    }

    public function r($id)
    {
        return $this->regions[$id];
    }

    public function checkRaid($from, $to)
    {
        if (!$this->_neighs($from, $to)) {
            return;
        }

        $rules = array(
            Game_Region::Water => array(Game_Region::Port, Game_Region::Land),
            Game_Region::Port => array(Game_Region::Water),
            Game_Region::Land => array(Game_Region::Land)
        );

        $from = $this->r($from);
        $to = $this->r($to);

        return in_array($to->type, $rules[$from->type]);
    }

    public function assertMarchPossible($hid, $from, $to)
    {
        $rules = array(
            Game_Region::Water => array(Game_Region::Port, Game_Region::Water),
            Game_Region::Port => array(Game_Region::Water),
            Game_Region::Land => array(Game_Region::Land)
        );

        $source = $this->r($from);
        $target = $this->r($to);

        if (
            !in_array($target->type, $rules[$source->type])
            || $target->check(Game_Region::Port) && $this->regionOwner($to) != $hid
        ) {
            throw new Game_MapException("Wrong target region type `$from:{$source->type}` - `$to:{$target->type}`", Game_MapException::WRONG_TARGET_REGION);
        }

        if ($this->_neighs($from, $to)) {
            return;
        }

        if (!$source->check(Game_Region::Land)) {
            throw new Game_MapException("Wrong target region type: no way - not a land", Game_MapException::NO_WAY);
        }

        $this->_assertWaterWayExists($hid, $source, $target);
    }

    public function check($rid, $type)
    {
        return $this->r($rid)->type == $type;
    }

    public function regionOwner($rid)
    {
        return $this->r($rid)->owner;
    }

    public function availableMarchRoutes($hid)
    {
        $orders = $this->orders($hid, Game_Order::March);
        $routes = array();
        foreach ($orders as $rid => $order) {
            $routes[$rid] = array('routes' => array_map(function ($route) {
                return array('route' => $route);
            }, array_filter($this->availableMarchRoute($rid, $hid))));
        }
        return $routes;
    }

    public function availableMarchRoute($rid, $hid)
    {
        $region = $this->r($rid);
        switch ($region->type) {
            case Game_Region::Water:
                $waterTargets = $this->neighs($region, Game_Region::Water);
                $me = $this;
                $targets = array_filter(
                    $this->neighs($region, Game_Region::Port),
                    function ($r) use ($hid, $me) {
                        return $me->regionOwner($r) == $hid;
                    }
                );
                foreach ($waterTargets as $target) {
                    array_push($targets, $target);
                }
                $routes = array();
                foreach ($targets as $target) {
                    $routes[$target] = array($rid, $target);
                }
                return $routes;
            case Game_Region::Port:
                $targets = $this->neighs($region, Game_Region::Water);
                $routes = array();
                foreach ($targets as $target) {
                    $routes[$target] = array($rid, $target);
                }
                return ;
            case Game_Region::Land:
                $rids = array();
                $rids[$rid] = false;
                $route = array($rid);
                $this->shipRoutes($region, $hid, $rids, $route);
                return $rids;
        }
        throw new Exception("WTF! Wrong region type");
    }

    public function shipRoutes($region, $hid, &$rids, &$route)
    {
        $lands = $this->neighsNotIn($region, Game_Region::Land, $rids);

        foreach ($lands as $rid) {
            $r = $route;
            array_push($r, $rid);
            $rids[$rid] = $r;
        }

        $me = $this;
        $waters = array_filter(
            $this->neighsNotIn($region, Game_Region::Water, $rids),
            function ($r) use ($hid, $me) {
                return $me->regionOwner($r) == $hid;
            }
        );

        foreach ($waters as $rid) {
            $rids[$rid] = false;
            array_push($route, $rid);
            $this->shipRoutes($this->r($rid), $hid, $rids, $route);
            array_pop($route);
        }
    }

    public function neighsNotIn($region, $type, $list)
    {
        return array_filter(
            $this->neighs($region, $type),
            function ($r) use ($list) {
                return !isset($list[$r]);
            }
        );
    }

    public function neighs($region, $type)
    {
        $me = $this;
        return array_filter($region->neighs, function($r) use ($type, $me) {
            return $me->regions[$r]->check($type);
        });
    }

    private function _assertWaterWayExists($hid, $from, $to)
    {
        $neighs = $from->neighs;
        foreach ($neighs as $n) {
            if ($this->_leadToTarget($hid, $n, $to->id, $neighs)) {
                return;
            }
        }
        throw new Game_MapException("Wrong target region type: no way", Game_MapException::NO_WAY);
    }

    private function _leadToTarget($hid, $rid, $to, $neighs)
    {
        $region = $this->r($rid);
        $newNeighs = $region->neighs;
        if (in_array($to, $newNeighs)) {
            return true;
        }
        $me = $this;
        $newNeighs = array_filter(
            $newNeighs,
            function($nrid) use ($hid, $me, $neighs) {
                $nregion = $me->r($nrid);
                $army = $me->army($nrid);
                return !in_array($nrid, $neighs)
                    && $nregion->type == Game_Region::Water
                    && $army && $army->hid == $hid;
            }
        );
        foreach ($newNeighs as $n) {
            array_push($neighs, $n);
        }
        foreach ($newNeighs as $n) {
            if ($this->_leadToTarget($hid, $n, $to, $neighs)) {
                return true;
            }
        }
        return false;
    }

    private function _checkConsistency()
    {
        foreach ($this->regions as $id => $region) {
            foreach ($region->neighs as $n) {
                if (!$this->_neighs($id, $n)) {
                    throw new Exception("Map is broken: neighbourhood ($id, $n)");
                }
                if (
                    $region->type == Game_Region::Port
                    && (!in_array($region->town, $region->neighs))
                ) {
                    throw new Exception("Map is broken: port ($id)");
                }
            }
        }
    }

    private function _neighs($r1, $r2)
    {
        return in_array($r2, $this->r($r1)->neighs)
            && in_array($r1, $this->r($r2)->neighs);
    }
}

