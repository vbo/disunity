<?php
class Test_PlanningPhase extends Test
{
    protected function _testNoOrder()
    {
        $this->_turn(House::Stark, array(
            'orders' => array(
                3 => Game_Order::RaidBasic,
                4 => Game_Order::RaidBasic,
                47 => Game_Order::RaidBasic,
            )), 'Game_OrdersException_NoOrder');

        $this->_turn(House::Lannister, array(
            'orders' => array(
                19 => Game_Order::DefenseStar,
                51 => Game_Order::DefenseBasic,
                21 => Game_Order::DefenseStar,
            )), 'Game_OrdersException_NoOrder');
    }

    protected function  _testOrderCount()
    {
        $this->_turn(House::Baratheon, array(
            'orders' => array(
                3 => Game_Order::SupportBasic,
                4 => Game_Order::SupportBasic
            )), 'Game_OrdersException_BadCount');
    }

    protected function _testLackOfStars()
    {
        $this->_turn(House::Baratheon, array(
            'orders' => array(
                56 => Game_Order::RaidStar,
                59 => Game_Order::DefenseStar,
                27 => Game_Order::MarchBasic,
            )), 'Game_OrdersException_LackOfStars');

        $this->_turn(House::Stark, array(
            'orders' => array(
                3 => Game_Order::DefenseStar,
                4 => Game_Order::SupportStar,
                47 => Game_Order::RaidStar
            )), 'Game_OrdersException_LackOfStars');
    }

    protected function _testNoArmy()
    {
        $this->_turn(House::Baratheon, array(
            'orders' => array(
                57 => Game_Order::RaidStar,
                59 => Game_Order::DefenseBasic,
                27 => Game_Order::MarchBasic,
            )), 'Game_OrdersException_NoArmy');
    }

    protected function _testOrdersReallySets()
    {
        $this->_turn(House::Baratheon, array(
            'orders' => array(
                56 => Game_Order::MarchBasic,
                59 => Game_Order::MarchStar,
                27 => Game_Order::MarchWeak,
            )));
        $this->_assertOrder(56, Game_Order::MarchBasic);
        $this->_assertOrder(59, Game_Order::MarchStar);
        $this->_assertOrder(27, Game_Order::MarchWeak);
    }
}
