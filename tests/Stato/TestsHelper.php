<?php

/*
 * Include PHPUnit dependencies
 */
require_once 'PHPUnit/Framework.php';

ob_start();

error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Europe/Paris');

mb_internal_encoding("UTF-8");

/*
 * Prepend the Stato library and tests directories to the include_path
 */
$path = array(
    dirname(__FILE__).'/..',
    dirname(__FILE__).'/../../library',
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

/*
 * Autoloading
 */
function __autoload($className) {
    $path = str_replace('\\', '/', $className).'.php';
    require $path;
}
