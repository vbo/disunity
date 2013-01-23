<?php

class Game_ArmyException extends Exception
{
    const LACK_OF_UNITS = 1;
}

class Game_Army
{
    public $hid;
    public $units;

    public function __construct($hid, $units)
    {
        $this->hid = $hid;
        $this->units = $units;
    }

    public function sub($units)
    {
        foreach ($units as $unit) {
            $place = array_search($unit, $this->units);
            if ($place === false) {
                throw new Game_ArmyException("Lack of units", Game_ArmyException::LACK_OF_UNITS);
            }
            array_splice($this->units, $place, 1);
        }
    }

    public function add($units)
    {
        foreach ($units as $unit) {
            array_push($this->units, $unit);
        }
    }
}

