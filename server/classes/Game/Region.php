<?php

class Game_RegionException extends Exception {
    const WRONG_ARMY_MOVE = 1;
    const HERE_IS_NO_ARMY = 2;
}
class Game_RegionExceptionConstructionUnit extends Game_RegionException {}
class Game_RegionExceptionConstructWrongOwner extends Game_RegionException {}
class Game_RegionExceptionUpgradeUnit extends Game_RegionException {}
class Game_RegionExceptionUpgradeUnitTo extends Game_RegionException {}

class Game_Region extends Game_Entity
{
    const Water = 2;
    const Land = 1;
    const Port = 3;

    const None = 1;
    const Castle = 2;
    const Stronghold = 3;

    public $id;
    public $name;
    public $type;
    public $fort;
    public $crowns;
    public $supplies;
    public $neighs = array();
    public $town;
    public $lord;
    public $owner;
    public $homeland;
    public $power;
    public $army;
    public $order;
    public $enemy;
    public $enemyHouse;
    public $enemyHomesys;

    public $style;

    protected static $exportProps = array(
        'id', 'name', 'type', 'fort', 'crowns', 'supplies', 'neighs',
        'town', 'lord', 'owner', 'homeland', 'power', 'army', 'order', 'style', 'enemy', 'enemyHouse');

    public function __construct($id, $config, $army, $lord, $homeland)
    {
        $this->lord = $lord;
        if ($homeland) {
            $this->owner = $homeland;
            $this->power = 1;
            $this->homeland = 2;
        }
        if ($army) {
            $hid = $army['hid'];
            $units = $army['units'];
            $this->army = new Game_Army($hid, $units);
            $this->owner = $hid;
        }
        $this->id = $id;
        foreach ($config as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function construct($hid, $units)
    {
        if ($this->owner && $this->owner != $hid) {
            throw new Game_RegionExceptionConstructWrongOwner("Wrong region owner: {$this->owner}");
        }
        $cost = 0;
        $army = new Game_Army($hid);
        foreach ($units as $unit) {
            if ($this->type == self::Land && $unit == Game_Unit::Robot || $this->type != self::Land && $unit != Game_Unit::Robot) {
                throw new Game_RegionExceptionConstructionUnit("Wrong construction unit `$unit` at region: {$this->id}");
            }
            $instance = Game_Unit::factory($unit);
            $cost += $instance->cost;
            $army->units[] = $instance;
        }
        $this->addUnits($army);
        return $cost;
    }

    public function upgradeUnit($from, $to)
    {
        if ($from != Game_Unit::Fighter) {
            throw new Game_RegionExceptionUpgradeUnit("Couldn't upgrade this unit: `$from`");
        }
        if (!in_array($to, array(Game_Unit::Station, Game_Unit::Cruiser))) {
            throw new Game_RegionExceptionUpgradeUnitTo("Couldn't upgrade to this unit: `$to`");
        }
        $hid = $this->owner;
        $this->subUnits(array($from));
        $army = new Game_Army($hid, array($to));
        $this->addUnits($army);
        $cost = 1;
        return $cost;
    }

    public function setOrder($order) {
        $this->order = $order;
    }

    public function unsetOrder() {
        $this->order = null;
    }

    public function setEnemy($units, $hid) {
        $this->enemyHouse = $hid;
        $this->enemy = $units;
    }

    public function unsetEnemy() {
        $this->enemyHomesys = null;
        $this->enemy = null;
    }

    public function subUnits($units) {
        if (!$this->army) {
            throw new Game_RegionException("There is no army here: {$this->id}", Game_RegionException::HERE_IS_NO_ARMY);
        }
        $subed = $this->army->sub($units);
        if (!$this->army->units) {
            $this->army = null;
            if (!$this->power && !$this->army) {
                $this->owner = null;
            }
        }
        return $subed;
    }

    public function defeated()
    {
        $this->power = null;
        $this->army = null;
        $this->homeland = null;
        $this->owner = null;
    }

    public function addUnits(Game_Army $army) {
        if (!$this->army) {
            $this->army = $army;
            $this->owner = $army->hid;
        } else {
            if ($this->army->hid != $army->hid) {
                throw new Game_RegionException("Wrong army move", Game_RegionException::WRONG_ARMY_MOVE);
            }
            $this->army->add($army);
        }
    }

    public function setLord($str)
    {
        $this->lord = $str;
    }

    public function check($type)
    {
        return $this->type == $type;
    }

    public function cleanUp()
    {
        $this->unsetOrder();
    }
}

