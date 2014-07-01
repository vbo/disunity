<?php
require_once dirname(__FILE__) . '/../server/bootstrap.php';

$action = @$_REQUEST['action'];
if (!$action) {
    die();
}

session_start();
$ctrl = new Api();
$input = $_REQUEST;
unset($input['action']);
$result = call_user_func(array($ctrl, "do$action"), $input);
$output = array("result" => "error");
if ($result !== false) {
    $output["result"] = "success";
    $output["data"] = $result;
}

header("Content-type: text/plain");
echo json_encode($output);


class Api
{
    private $_sessionConfig;
    private $_gameConfig;

    public function __construct()
    {
        $this->_sessionConfig = $this->_loadConfig('session.json');
        $this->_gameConfig = $this->_loadConfig('game.json');
    }

    public function doAuth($input)
    {
        $player = @$input['player'];
        if (!$player) {
            return false;
        }
        $house = @$this->_sessionConfig['players'][$player];
        if (!$house) {
            return false;
        }
        $_SESSION['house'] = $house;
        return array(
            "house_id" => $house,
        );
    }

    public function doGetEvent($input)
    {
        $house = @$_SESSION['house'];
        if (!$house) {
            return false;
        }
        $world = $this->_loadGame();
        $state = $world->publish($house);
        return array(
            "state" => $state,
            "event" => $world->currentPhaseData($house)
        );
    }

    public function doTurn($input)
    {
        $house = @$_SESSION['house'];
        if (!$house) {
            return false;
        }
        $world = $this->_loadGame();
        try {
            $world->turn($house, $input);
            $world->commit();
        } catch (Exception $e) {
            print_r($e);
            return false;
        }
        return array();
    }

    private function _loadConfig($conf)
    {
        return json_decode(file_get_contents(dirname(__FILE__) . '/../config/' . $conf), true);
    }

    private function _sav()
    {
        $dir = "/tmp";
        return "{$dir}/{$this->_sessionConfig['name']}.sav";
    }

    private function _loadGame()
    {
        $storage = new Game_Storage($this->_sav());
        if (!$storage->exists()) {
            $state = new Game($storage, count($this->_sessionConfig['players']), $this->_gameConfig);
            $state->commit();
        }
        return $storage->load();
    }
}


