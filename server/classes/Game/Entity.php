<?php

class Game_Entity
{
    public function export()
    {
        return $this->_export();
    }

    private function _export()
    {
        $result = array();
        foreach (static::$exportProps as $name)
        {
            $val = $this->{$name};
            $result[$name] = $this->_exportVal($val);
        }
        return $result;
    }

    private function _exportVal($val)
    {
        if ($val instanceof Game_Entity) {
            return $val->export();
        }
        if (is_array($val)) {
            $result = array();
            foreach ($val as $k => $v) {
                $result[$k] = $this->_exportVal($v);
            }
            return $result;
        }
        return $val;
    }
}

