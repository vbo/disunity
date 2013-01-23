<?php

class Game_Node_Action extends Game_Node
{
    protected function _init()
    {
        return new Game_Node_Raid();
    }

    public function childFinished($node)
    {
        if ($node instanceof Game_Node_Raid) {
            return new Game_Node_March();
        }
        if ($node instanceof Game_Node_March) {
            return new Game_Node_Power();
        }
        return -1;
    }
}
