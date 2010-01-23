#!/usr/bin/php

<?php

set_include_path(dirname(__FILE__) . '/library' . PATH_SEPARATOR . get_include_path());

function __autoload($className) {
    $path = str_replace('_', '/', $className).'.php';
    require $path;
}

Stato_Cli_CommandRunner::main($_SERVER['argv']);

exit(0);
