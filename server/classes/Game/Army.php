<?php

class Game_ArmyException extends Exception
{
    const LACK_OF_UNITS = 1;
}

class Game_Army extends Game_Entity
{
    const Fighter = 1;
    const Cruiser = 2;
    const Robot = 3;
    const Station = 4;

    public static $forceTable = array(
        self::Fighter => 1,
        self::Cruiser => 2,
        self::Robot => 1,
        self::Station => 0
    );

    public static $costTable = array(
        self::Fighter => 1,
        self::Cruiser => 2,
        self::Robot => 1,
        self::Station => 2
    );

    const STATION_FORT_ATTACK_BONUS = 4;

    public $hid;
    public $units;

    protected static $exportProps = array('hid', 'units');

    public function __construct($hid, array $units)
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

    public static function attackComponents($hid, $units, $fort)
    {
        return self::_components($hid, $units, true, $fort);
    }

    public static function defenceComponents($hid, $units)
    {
        return self::_components($hid, $units, false, Game_Region::None);
    }

    private static function _components($hid, $units, $isAttack, $fort)
    {
        // todo: cover this stuff in unit tests
        $components = array();
        foreach ($units as $unit) {
            $components[] = array(
                'type' => 'unit',
                'hid' => $hid,
                'unit' => $unit,
                'bonus' => self::unitForce($unit, $isAttack, $fort)
            );
        }
        return $components;
    }
}

