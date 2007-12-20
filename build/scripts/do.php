<?php

$args = $_SERVER['argv'];
// shift file name
array_shift($args);
$command_name = array_shift($args);

if ($command_name == 'run_app_tests') $_SERVER['STATO_ENV'] = 'test';

include(dirname(__FILE__).'/../conf/boot.php');
SCommand::load($command_name, $args)->execute();

?>
