<?php

include(dirname(__FILE__).'/../conf/boot.php');
SCommand::load('migrate')->execute();

?>
