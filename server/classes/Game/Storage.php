<?php

class Game_Storage
{
    private $_file;

    public function __construct($file)
    {
        $this->_file = $file;
    }

    public function save(Game $state)
    {
        file_put_contents($this->_file, serialize($state));
    }

    /**
     * @return Game
     */
    public function load()
    {
        return unserialize(file_get_contents($this->_file));
    }

    public function exists()
    {
        return file_exists($this->_file);
    }

    public function clear()
    {
        return @unlink($this->_file);
    }
}

