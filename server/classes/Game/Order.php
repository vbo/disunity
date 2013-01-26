<?php

class Game_Order extends Game_Entity
{
    const Raid = 1;
    const March = 2;
    const Defense = 3;
    const Support = 4;
    const Power = 5;

    const RaidBasic = 11;
    const RaidStar = 12;

    const MarchWeak = 21;
    const MarchBasic = 22;
    const MarchStar = 23;

    const DefenseBasic = 31;
    const DefenseStar = 32;

    const SupportBasic = 41;
    const SupportStar = 42;

    const PowerBasic = 51;
    const PowerStar = 52;

    // used for hacking around hidden/public information in Game::publish()
    const Hidden = 100500;

    public $hid;
    public $id;
    public $name;
    public $type;
    public $star;
    public $bonus;
    public $icon;

    protected static $exportProps = array('hid', 'id', 'name', 'type', 'star', 'bonus', 'icon');

    public function __construct($hid, $config)
    {
        $this->hid = $hid;
        foreach ($config as $k => $v)
        {
            $this->{$k} = $v;
        }
        $this->type = floor($this->id / 10);
    }

    static function type($id)
    {
        return floor($id / 10);
    }

    public function check($orderType)
    {
        return $this->type == $orderType;
    }

    static function checkRaid($from, $to)
    {
        $rules = array(
            self::RaidBasic => array(self::Power, self::Support, self::Raid),
            self::RaidStar => array(self::Power, self::Support, self::Raid, self::Defense),
        );

        return $to
            && $from->hid != $to->hid
            && in_array(self::type($to), $rules[$from->type]);
    }
}

