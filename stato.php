<?php

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__))));

require_once(ROOT_DIR.'/common/common.php');
require_once(ROOT_DIR.'/cli/cli.php');

$args = $_SERVER['argv'];
// shift file name
array_shift($args);

$command_name = array_shift($args);
SCommand::load($command_name, $args)->execute();

?>
