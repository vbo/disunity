<?php

class Game_NodeStack
{
    /**
     * @var Game_Node[]
     */
    private $_storage = array();

    public function push(Game_Node $node)
    {
        array_push($this->_storage, $node);
    }

    /**
     * @return Game_Node
     */
    public function pop()
    {
       return array_pop($this->_storage);
    }

    /**
     * @return Game_Node
     */
    public function end()
    {
        return end($this->_storage);
    }
}
