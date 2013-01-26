<?php

class Game_Node_Fight extends Game_Node
{
    const HOMELAND_BONUS = 2;

    public $source;
    public $target;
    public $units;

    public $attacker;
    public $defender;

    public $bonuses;
    public $winner;
    public $looser;

    public function __construct($units, $source, $target)
    {
        $this->units = $units;
        $this->source = $source;
        $this->target = $target;
        $this->attacker = $source->owner;
        $this->defender = $target->owner;

        $attOrder = $this->source->order;
        $defOrder = $this->target->order;
        $this->bonuses = array(
            $this->attacker => array(
                'force' => Game_Army::attackForce($this->units, $this->target->fort),
                'order' => $attOrder->bonus,
                'support' => 0
            ),
            $this->defender => array(
                'force' => Game_Army::defenceForce($this->target->army->units),
                'order' => $defOrder && $defOrder->check(Game_Order::Defense) ? $defOrder->bonus : 0,
                'homeland' => $target->homeland ? self::HOMELAND_BONUS : 0,
                'support' => 0
            )
        );
    }

    public function _init()
    {
        return new Game_Node_Support($this->attacker, $this->defender, $this->target, $this->bonuses);
    }

    public function childFinished($node)
    {
        if ($node instanceof Game_Node_Support) {
            $this->bonuses = $node->bonuses;
            $attackerScore = 0;
            foreach ($this->bonuses[$this->attacker] as $value) {
                $attackerScore += $value;
            }
            $defenderScore = 0;
            foreach ($this->bonuses[$this->defender] as $value) {
                $defenderScore += $value;
            }
            if ($attackerScore == $defenderScore) {
                list($winner, $looser) = $this->_game->tracks->trackSort(
                    Game_Track::Blade,
                    array($this->attacker, $this->defender));
            } elseif ($attackerScore > $defenderScore) {
                $winner = $this->attacker;
                $looser = $this->defender;
            } else {
                $winner = $this->defender;
                $looser = $this->attacker;
            }
            $this->winner = $winner;
            $this->looser = $looser;
            $this->source->unsetOrder();
            if ($winner == $this->defender) {
                // todo: needs to mark attacker units as injured
            } else {
                $this->target->unsetOrder();
                return new Game_Node_Retreat($looser, $this->target, $this->source);
            }
        }
        return -1;
    }
}

