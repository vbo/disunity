<?php

class Game_Node_Round extends Game_Node
{
    public $turned = array();

    protected function _init()
    {
        return $this->_game->round == 1
            ? new Game_Node_Planning()
            : new Game_Node_Westeros();
    }

    public function childFinished($node)
    {
        if ($node instanceof Game_Node_Westeros) {
            return new Game_Node_Planning();
        }
        if ($node instanceof Game_Node_Planning) {
            return new Game_Node_Action();
        }
        return -1;
    }
}

