<?php

define('STATO_ENV', 'test');
include(dirname(__FILE__).'/../conf/boot.php');
SCommand::load('run_app_tests')->execute();

?>
