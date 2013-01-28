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

    public function __construct($hid, $units, $source, $target)
    {
        $this->units = $units;
        $this->source = $source;
        $this->target = $target;
        $this->attacker = $hid;
        $this->defender = $target->owner;

        $attOrder = $this->source->order;
        $defOrder = $this->target->order;

        $attBonuses = array();
        $attBonuses[] = array(
            'type' => 'order',
            'hid' => $this->attacker,
            'order' => $attOrder,
            'bonus' => $attOrder->bonus
        );
        $attBonuses = array_merge($attBonuses, Game_Army::attackComponents($this->attacker, $this->units, $this->target->fort));

        $defBonuses = array();
        if ($defOrder && $defOrder->check(Game_Order::Defense)) {
            $defBonuses[] = array(
                'type' => 'order',
                'hid' => $this->defender,
                'order' => $defOrder,
                'bonus' => $defOrder->bonus
            );
        }
        $defBonuses = array_merge($defBonuses, Game_Army::defenceComponents($this->defender, $this->target->army->units));
        if ($target->homeland) {
            $defBonuses[] = array(
                'type' => 'homeland',
                'hid' => $this->defender,
                'bonus' => self::HOMELAND_BONUS
            );
        }

        $this->bonuses = array(
            $this->attacker => $attBonuses,
            $this->defender => $defBonuses
        );
    }

    public function _init()
    {
        $this->target->setEnemy($this->units, $this->attacker);
        return new Game_Node_Support($this->attacker, $this->defender, $this->target, $this->bonuses);
    }

    public function childFinished($node)
    {
        if ($node instanceof Game_Node_Support) {
            $this->bonuses = $node->bonuses;
            $attackerScore = 0;
            foreach ($this->bonuses[$this->attacker] as $value) {
                $attackerScore += $value['bonus'];
            }
            $defenderScore = 0;
            foreach ($this->bonuses[$this->defender] as $value) {
                $defenderScore += $value['bonus'];
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
        $this->target->unsetEnemy();
        return -1;
    }
}

