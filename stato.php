<?php

define('STATO_CORE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__))));

require_once(STATO_CORE_PATH.'/common/common.php');
require_once(STATO_CORE_PATH.'/cli/cli.php');

$args = $_SERVER['argv'];
// shift file name
array_shift($args);

$command_name = array_shift($args);
SCommand::load($command_name, $args)->execute();

?>
