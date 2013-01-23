<?php

abstract class Game_Node
{
    /**
     * @var Game
     */
    protected $_game;

    public function act($hid, $request)
    {
        $hid; $request;
        throw new Exception(get_class($this) . "::act() must not be called directly");
    }

    final public function init(Game $game)
    {
        $this->_game = $game;
        return $this->_init();
    }

    public function childFinished($node)
    {

    }

    final public function id()
    {
        $cl = explode('_', get_class($this));
        return strtolower(join("_", array_slice($cl, 2)));
    }

    public function data($hid)
    {
        $hid;
        return array();
    }

    protected function _init()
    {
        // pass
    }
}

