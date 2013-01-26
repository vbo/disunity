<?php

class Game_ArmyException extends Exception
{
    const LACK_OF_UNITS = 1;
}

class Game_Army extends Game_Entity
{
    const Troopers = 1;
    const Cruiser = 2;
    const Robot = 3;
    const Station = 4;

    public static $forceTable = array(
        self::Troopers => 1,
        self::Cruiser => 2,
        self::Robot => 1,
        self::Station => 0
    );

    const STATION_FORT_ATTACK_BONUS = 4;

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

    public static function unitForce($unit, $isAttack=false, $fort=Game_Region::None)
    {
        if ($isAttack && $unit == self::Station && $fort != Game_Region::None) {
            return self::STATION_FORT_ATTACK_BONUS;
        }
        return self::$forceTable[$unit];
    }

    public static function attackForce($units, $fort)
    {
        $force = 0;
        foreach ($units as $unit) {
            $force += self::unitForce($unit, true, $fort);
        }
        return $force;
    }

    public static function defenceForce($units)
    {
        $force = 0;
        foreach ($units as $unit) {
            $force += self::unitForce($unit);
        }
        return $force;
    }
}

