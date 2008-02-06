<?php

include(dirname(__FILE__).'/../conf/boot.php');
require(STATO_CORE_PATH.'/components/console/console.php');

SCommand::find_and_execute();

?>
