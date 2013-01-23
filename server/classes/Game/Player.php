<?php

class Game_PlayerException extends Exception {
    const NO_POWER = 8;
}

class Game_Player
{
    public $house;
    public $name;
    public $home;
    public $track;
    public $cards;
    public $resources;
    public $style;

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
        $this->resources['power']--;
    }

    public function addPower()
    {
        $this->resources['power']++;
    }
}

