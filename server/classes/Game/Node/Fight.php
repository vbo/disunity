<?php

class Game_Node_Fight extends Game_Node
{
    public $units;
    public $source;
    public $target;

    public function __construct($units, $source, $target)
    {
        $this->units = $units;
        $this->source = $source;
        $this->target = $target;
    }

    public function act($hid, $request)
    {
        // TODO: Implement act() method.
    }
}

