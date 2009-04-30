#!/usr/bin/php

<?php

set_include_path(__DIR__ . '/library' . PATH_SEPARATOR . get_include_path());

function __autoload($className) {
    $path = str_replace('\\', '/', $className).'.php';
    require $path;
}

use Stato\Cli\CommandRunner;

CommandRunner::main($_SERVER['argv']);

exit(0);
