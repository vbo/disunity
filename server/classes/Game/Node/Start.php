<?php

class Game_Node_StartException extends Exception
{
    const PLAYER_DUPLICATE = 1;
    const WRONG_HOUSE = 2;
    const HOUSE_DUPLICATE = 3;
}

class Game_Node_Start extends Game_Node
{
    const ROUNDS_COUNT = 10;

    protected function _init()
    {
        return $this->_startRound(1);
    }

    public function childFinished($roundNode)
    {
        return $this->_nextRound();
    }

    private function _nextRound()
    {
        if ($this->_game->round < self::ROUNDS_COUNT) {
            return $this->_startRound(++$this->_game->round);
        }
        // todo: return Game_Node_End
        return null;
    }

    private function _startRound($round)
    {
        $this->_game->round = $round;
        return new Game_Node_Round();
    }
}

