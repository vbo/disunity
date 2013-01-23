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
            )), null, function($game, $e) {
                return $e instanceof Game_OrdersException
                    && $e->getCode() == Game_OrdersException::NO_ORDER;
            });

        $this->_turn(House::Lannister, array(
            'orders' => array(
                19 => Game_Order::DefenseStar,
                51 => Game_Order::DefenseBasic,
                21 => Game_Order::DefenseStar,
            )), null, function($game, $e) {
                return $e instanceof Game_OrdersException
                    && $e->getCode() == Game_OrdersException::NO_ORDER;
            });
    }

    protected function  _testOrderCount()
    {
        $this->_turn(House::Baratheon, array(
            'orders' => array(
                3 => Game_Order::SupportBasic,
                4 => Game_Order::SupportBasic
            )), null, function($game, $e) {
                return $e instanceof Game_OrdersException
                    && $e->getCode() == Game_OrdersException::ORDERS_COUNT;
            }
        );
    }

    protected function _testLackOfStars()
    {
        $this->_turn(House::Baratheon, array(
            'orders' => array(
                56 => Game_Order::RaidStar,
                59 => Game_Order::DefenseStar,
                27 => Game_Order::MarchBasic,
            )), null, function($game, $e) {
                return $e instanceof Game_OrdersException
                    && $e->getCode() == Game_OrdersException::LACK_OF_STARS;
            });

        $this->_turn(House::Stark, array(
            'orders' => array(
                3 => Game_Order::DefenseStar,
                4 => Game_Order::SupportStar,
                47 => Game_Order::RaidStar
            )), null, function($game, $e) {
                return $e instanceof Game_OrdersException
                    && $e->getCode() == Game_OrdersException::LACK_OF_STARS;
        });
    }

    protected function _testNoArmy()
    {
        $this->_turn(House::Baratheon, array(
            'orders' => array(
                57 => Game_Order::RaidStar,
                59 => Game_Order::DefenseBasic,
                27 => Game_Order::MarchBasic,
            )), null, function($game, $e) {
                return $e instanceof Game_OrdersException
                    && $e->getCode() == Game_OrdersException::NO_ARMY;
            });
    }
}
