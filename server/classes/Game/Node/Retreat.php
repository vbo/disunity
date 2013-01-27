<?php

class Game_Node_RetreatException extends Exception {}
class Game_Node_RetreatExceptionWrongRegion extends Game_Node_RetreatException {}

class Game_Node_Retreat extends Game_Node
{
    public $hid;
    public $from;
    public $exclude;

    public $routes;

    public function __construct($hid, $from, $exclude)
    {
        $this->hid = $hid;
        $this->from = $from;
        $this->exclude = $exclude;
    }

    public function data($hid)
    {
        return array(
            'cur_player' => $this->hid,
            'routes' => $this->routes,
            'from' => $this->from->id
        );
    }

    public function _init()
    {
        $this->routes = array();
        $routes = array_filter($this->_game->map->availableMarchRoute($this->from->id, $this->hid));
        foreach ($routes as $rid => $route) {
            if ($rid == $this->exclude->id) {
                continue;
            }
            $region = $this->_game->map->r($rid);
            if ($region->owner && $region->owner != $this->hid) {
                continue;
            }
            $this->routes[$rid] = $route;
        }
        if (!count($this->routes)) {
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
        $this->_game->map->r($request->rid)->addUnits($this->hid, $army->units);
        return -1;
    }
}

