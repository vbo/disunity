<?php

class Game_ArmyException extends Exception
{
    const LACK_OF_UNITS = 1;
}

class Game_Army extends Game_Entity
{
    public $hid;
    public $units;

    protected static $exportProps = array('hid', 'units');

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
        return $this->units;
    }

    public function add($units)
    {
        foreach ($units as $unit) {
            array_push($this->units, $unit);
        }
    }
}

