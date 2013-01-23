<?php

class Game_StateException extends Exception
{
    const HOUSE_NOT_FOUND = 10;
}

class Game
{
    /**
     * @var Game_Map
     */
    public $map;

    /**
     * @var \Game_Player[]
     */
    public $players;

    /**
     * @var Game_Orders
     */
    public $orders;

    /**
     * @var Game_Track
     */
    public $tracks;

    /**
     * @var Game_NodeStack
     */
    public $stack;

    /**
     * Current round number
     * @var int
     */
    public $round;

    public function __construct(Game_Storage $storage, $playersNumber, $config)
    {
        $this->stack = new Game_NodeStack();
        $this->_storage = $storage;
        if (!is_array($config)) {
            $config = json_decode(file_get_contents($config), 1);
        }
        $this->_loadConfig($playersNumber, $config);
        $this->_stackPush(new Game_Node_Start());
    }

    public function commit()
    {
        return $this->_storage->save($this);
    }

    public function currentPhaseData($hid)
    {
        $player = @$this->players[$hid];
        if (!$player) {
            throw new Game_StateException("House not found!", Game_StateException::HOUSE_NOT_FOUND);
        }
        $node = $this->stack->end();
        return array('phase' => $node->id(), 'data' => $node->data($hid));
    }

    public function publish($hid)
    {
        $hid; // todo: perform actual private data filtering
        return $this;
    }

    public function turn($hid, array $request)
    {
        $result = $this->stack->end()->act($hid, (object) $request);
        $this->_processStack($result);
    }

    private function _loadConfig($playersNumber, $config)
    {
        $players = array();
        $homeRegions = array();
        $armies = array();
        // todo: we need to know houseIds order from config
        foreach (range(1, $playersNumber) as $hid) {
            $houseConfig = $config['houses'][$hid];
            $players[$hid] = new Game_Player($hid, $houseConfig);
            $homeRegions[$houseConfig['home']] = $hid;
            foreach ($houseConfig['army'] as $rid => $units) {
                $armies[$rid] = array('hid' => $hid, 'units' => $units);
            }
        }
        $lords = @$config['lords'][$playersNumber];
        $regions = $config['regions'];
        $map = new Game_Map($regions, $homeRegions, $armies, $lords);
        $this->players = $players;
        $this->map = $map;
        $this->orders = new Game_Orders($config['orders']);
        $this->tracks = new Game_Track($players, $config['stars'][$playersNumber]);
    }

    private function _stackPush(Game_Node $node)
    {
        // todo: maybe it must be implemented in stack?!!
        $this->stack->push($node);
        $result = $node->init($this);
        $this->_processStack($result);
    }

    private function _stackPop()
    {
        $node = $this->stack->pop();
        $result = $this->stack->end()->childFinished($node);
        $this->_processStack($result);
    }

    private function _processStack($result)
    {
        if ($result instanceof Game_Node) {
            $this->_stackPush($result);
        } elseif ($result == -1) {
            $this->_stackPop();
        }
    }
}

