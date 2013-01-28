<?php

class Game_PlayerException extends Exception {}
class Game_PlayerExceptionNoPower extends Game_PlayerException {}

class Game_Player extends Game_Entity
{
    public $house;
    public $name;
    public $home;
    public $track;
    public $cards;
    public $resources;
    public $style;

    protected static $exportProps = array('house', 'name', 'home', 'track', 'cards', 'resources', 'style');

    public function __construct($house, $config)
    {
        $this->house = $house;
        foreach (array_keys((array) $this) as $k) {
            $v = @$config[$k];
            if ($v !== null) {
                $this->{$k} = $v;
            }
        }
    }

    public function power()
    {
        return $this->resources['power'];
    }

    public function subPower()
    {
        if (!$this->resources['power']) {
            throw new Game_PlayerExceptionNoPower('Lack of power');
        }
        $this->resources['power']--;
    }

    public function addPower()
    {
        $this->resources['power']++;
    }
}

