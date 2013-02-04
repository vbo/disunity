<?php

class Game_ArmyException extends Exception
{
    const LACK_OF_UNITS = 1;
}

class Game_Army extends Game_Entity
{
    public $hid;
    public $units = array();

    protected static $exportProps = array('hid', 'units');

    public function __construct($hid, array $unitTypes=array())
    {
        $this->hid = $hid;
        foreach ($unitTypes as $type) {
            $unit = Game_Unit::factory($type);
            $this->units[] = $unit;
        }
    }

    public function sub($unitTypes)
    {
        $army = new self($this->hid);
        foreach ($unitTypes as $type) {
            $found = false;
            for($i = 0, $len = count($this->units); $i < $len; $i++) {
                if ($this->units[$i]->is($type)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                throw new Game_ArmyException("Lack of units", Game_ArmyException::LACK_OF_UNITS);
            }
            $spliced = array_splice($this->units, $i, 1);
            $army->units[] = $spliced[0];
        }
        return $army;
    }

    public function add($army)
    {
        foreach ($army->units as $unit) {
            $this->units[] = $unit;
        }
    }

    public static function unitForce($unit, $isAttack=false, $fort=Game_Region::None)
    {
        if ($isAttack && $fort != Game_Region::None) {
            return $unit->fortAttack;
        }
        return $unit->attack;
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
        foreach ($units->units as $unit) {
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

