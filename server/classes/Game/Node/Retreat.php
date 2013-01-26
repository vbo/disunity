<?php

class Game_Node_RetreatException extends Exception {}
class Game_Node_RetreatExceptionWrongRegion extends Game_Node_RetreatException {}

class Game_Node_Retreat extends Game_Node
{
    public $hid;
    public $from;
    public $exclude;

    public $regions;

    public function __construct($hid, $from, $exclude)
    {
        $this->hid = $hid;
        $this->from = $from;
        $this->exclude = $exclude;
    }

    public function _init()
    {
        $this->regions = array();
        $routes = array_filter($this->_game->map->availableMarchRoute($this->from->id, $this->hid));
        foreach ($routes as $rid => $route) {
            if ($rid == $this->exclude->id) {
                continue;
            }
            $region = $this->_game->map->r($rid);
            if ($region->owner && $region->owner != $this->hid) {
                continue;
            }
            $this->regions[$rid] = $region;
        }
        if (!count($this->regions)) {
            $from->defeated();
            return -1;
        }
    }

    public function act($hid, $request)
    {
        if ($hid != $this->hid) {
            throw new Exception("Hack! $hid != {$this->hid}");
        }

        if (!array_key_exists($request->rid, $this->routes)) {
            throw new Game_Node_RetreatExceptionWrongRegion("Wrong retreat region: {$request->rid}");
        }

        $army = $this->from->army;
        $this->from->defeated();
        $this->regions[$request->rid]->addUnits($this->hid, $army->units);
        return -1;
    }
}

