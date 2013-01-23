<?php
abstract class Test
{
    /**
     * @var Game_Storage
     */
    private $_storage;

    public function execute()
    {
        $conf = dirname(__FILE__) . "/../../config/game.json";
        $file = dirname(__FILE__) . '/../../../../tmp/test.game';
        $storage = $this->_storage = new Game_Storage($file);
        $storage->clear();
        $world = new Game($storage, $this->_playersNum(), $conf);
        $world = $this->_prepare($world);
        $world->commit();
        $this->_test($world);
        $this->_end();
    }

    final protected function _test($world)
    {
        $world;
        $ref = new ReflectionClass($this);
        $methods = $ref->getMethods();
        foreach ($methods as $method) {
            $matched = preg_match("/_test[A-Z].*/", $method->name);
            if ($matched) {
                $this->{$method->name}();
            }
        }
    }

    protected function _end()
    {
        echo "\n";
    }

    protected function _prepare($world)
    {
        return $world;
    }

    protected function _playersNum()
    {
        return 3;
    }

    protected function _turn($hid, $request, $callback=null, $excallback=null)
    {
        $game = $this->_storage->load();
        try {
            ob_start();
            $game->turn($hid, $request);
            ob_end_clean();
        } catch (Exception $e) {
            if ($excallback) {
                $r =  $excallback($game, $e)
                    ? '.'
                    : 'E';
                print $r;
                if ($r == 'E') {
                    // $node = end($world->stack);
                    //throw $e;
                }
                return;
            }
            //throw $e;
        }
        if ($excallback) {
            print 'E';
            return;
        }
        if ($callback) {
            print $callback($game)
                ? '.'
                : 'E';
        }
    }
}
