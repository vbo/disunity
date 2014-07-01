<?php

abstract class Game_Unit
{
    // types:
    const Fighter = 'fighter';
    const Cruiser = 'cruiser';
    const Robot = 'robot';
    const Station = 'station';

    public $attack;
    public $fortAttack;
    public $cost;
    public $type;

    public static function factory($type)
    {
        $classname = self::type2class($type);
        return new $classname();
    }

    public function is($type)
    {
        return $this->type == $type;
    }

    public static function type2class($type) {
        return __CLASS__ . '_' . ucfirst($type);
    }
}
