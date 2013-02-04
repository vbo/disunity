<?php
abstract class Test
{
    /**
     * @var Game_Storage
     */
    private $_storage;
    private $_errorBuffer = array();
    protected $_game;

    final public function execute()
    {
        $conf = dirname(__FILE__) . "/../../config/game.json";
        $file = dirname(__FILE__) . '/../../../../tmp/test.game';
        $storage = $this->_storage = new Game_Storage($file);

        $ref = new ReflectionClass($this);
        $methods = $ref->getMethods();
        foreach ($methods as $method) {
            $storage->clear();
            $world = new Game($storage, $this->_playersNum(), $conf);
            $world = $this->_prepare($world);
            $world->commit();
            $matched = preg_match("/_test[A-Z].*/", $method->name);
            if ($matched) {
                try {
                    $this->{$method->name}();
                    echo '.';
                } catch (AssertException $e) {
                    echo 'E';
                    $this->_errorBuffer[] = array($method->name, $e);
                }
            }
        }
        $this->_end();
    }

    protected function _end()
    {
        echo "\n";
        foreach ($this->_errorBuffer as $err) {
            list($method, $e) = $err;
            echo get_class($this), '::', $method, "\n", $e, "\n------------------------------\n";
        }
    }

    protected function _prepare($world)
    {
        return $world;
    }

    protected function _playersNum()
    {
        return 3;
    }

    protected function _turn($hid, $request, $exceptionClass=null)
    {
        $this->_game = $game = $this->_storage->load();
        $e = null;
        try {
            ob_start();
            $game->turn($hid, $request);
            ob_end_clean();
        } catch (Exception $e) {
            // pass
        }
        if ($exceptionClass) {
            if (!$e || get_class($e) != $exceptionClass) {
                throw new AssertException("Exception expected: {$exceptionClass}, given <{$e}>");
            }
        } else {
            if ($e) {
                throw new AssertException("Unexpected exception: {$e}");
            }
        }
    }

    protected function _assertOrder($rid, $typeId=null)
    {
        $order = $this->_game->map->r($rid)->order;
        if (!$order || ($typeId && !$order->is($typeId))) {
            throw new AssertException("Order expected: <$typeId>, given <{$order->id}>");
        }
    }
}

class AssertException extends Exception {};