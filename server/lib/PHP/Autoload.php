<?php

/**
 * Simple and fast class autoloading.
 */
class PHP_Autoload
{
	/**
	 * Initializes the autoloading system.
	 *
	 * @return void
	 */
    public static function initialize()
    {
        spl_autoload_register(array(__CLASS__, '_autoload'));
    }


    /**
     * Loads a specified file. Throws an exception if the file
     * is not found.
     *
     * @param string $fname
     * @return bool
     */
    public static function loadFile($fname)
    {
        if (!@fopen($fname, 'r', true)) {
            throw new PHP_Autoload_Exception("Cannot load $fname");
        }
        return require_once($fname);
    }

    /**
     * Trying to get real path to the file
     *
     * @param string $fname
     * @return full-path
     */
    public static function getRealPath($fname)
    {
        if (!@fopen($fname, 'r', true)) {
            throw new PHP_Autoload_Exception("Cannot get real path $fname");
        }
        if (preg_match('{^(\w:)?[/\\]}s', $fname)) {
        	return $fname;
        }
        $paths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $path) {
            $path = realpath($path . '/' . $fname);
            if (file_exists($path)) {
                return $path;
            }
        }
        return false;
    }

    /**
     * Return true, if class is defined, without calling autoload
     *
     * @param string $className
     * @return boolean
     */
    public static function classDefined($className)
    {
        $className = strtolower($className);
        foreach(get_declared_classes() as $c) {
            if ($className === strtolower($c)) {
                return true;
            }
        }
        return false;
    }


    /**
     * Main autoloading method.
     * In case of autoloading error - throws an exception.
     *
     * @param string $className
     * @return bool
     */
    public static function _autoload($className)
    {
        if ($className == "parent") {
            // Hm-mmm... autoload bug?
            return true;
        } else {
            $fname = str_replace('_', '/', $className) . '.php';
        }
        try {
            return self::loadFile($fname);
        } catch (PHP_Autoload_Exception $e) {
            $stack = debug_backtrace();
            if (!strcasecmp(@$stack[2]['function'], 'class_exists')) {
                return false;
            } else {
                throw new PHP_Autoload_Exception($e->getMessage() . " (class $className)");
            }
        }
    }
}

class PHP_Autoload_Exception extends Exception
{
}
