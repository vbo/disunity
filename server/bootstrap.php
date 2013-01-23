<?php
// Initialize library directories.
$root = dirname(__FILE__);
set_include_path(implode(PATH_SEPARATOR, array_merge(
    array(
        $root . '/classes',
        $root . '/lib',
        get_include_path(),
    )
)));
require_once $root . '/lib/PHP/Autoload.php';
PHP_Autoload::initialize();