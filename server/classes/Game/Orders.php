<?php

class Game_OrdersException extends Exception {}
class Game_OrdersException_BadCount extends Game_OrdersException {}
class Game_OrdersException_NoArmy extends Game_OrdersException {}
class Game_OrdersException_NoOrder extends Game_OrdersException {}
class Game_OrdersException_LackOfStars extends Game_OrdersException {}

class Game_Orders
{
    public $orders;
    public $restrictions = array();

    public function __construct($orders)
    {
        $this->orders = array();
        foreach ($orders as $id => $order) {
            $order['id'] = $id;
            $order['type'] = Game_Order::type($id);
            for ($i = 0; $i < $order['count']; $i++) {
                $this->orders[] = $order;
            }
        }
    }

    public function processOrders($hid, $orders, $stars, $armies)
    {
        if (count($orders) != count($armies)) {
            throw new Game_OrdersException_BadCount("Order error: orders count is not valid!");
        }

        $allOrders = $this->available();
        $processed = array();

        foreach($orders as $region => $order) {
            if (!isset($armies[$region])) {
                throw new Game_OrdersException_NoArmy( "Order error: has no army at region `$region`");
            }

            $ords = array_filter($allOrders, function ($v) use ($order) {
                return $v['id'] == $order;
            });

            if (!$ords) {
                throw new Game_OrdersException_NoOrder("Order error: there is no such order: `$order`");
            }

            $ordsKeys = array_keys($ords);
            $key = $ordsKeys[0];
            unset($allOrders[$key]);
            $ord = $ords[$key];

            if ($ord['star']) {
                if (!$stars) {
                    throw new Game_OrdersException_LackOfStars("Order error: Lack of stars");
                }
                $stars--;
            }

            $processed[$region] = new Game_Order($hid, $ord);
        }

        return $processed;
    }

    public function setRestriction($orderType)
    {
        $this->restrictions[] = $orderType;
    }

    public function clearRestrictions()
    {
        $this->restrictions = array();
    }

    public function available()
    {
        $avail = array();
        foreach ($this->orders as $id => $order) {
            if (!in_array($order['type'], $this->restrictions)) {
                $avail[] = $order;
            }
        }
        return $avail;
    }
}

