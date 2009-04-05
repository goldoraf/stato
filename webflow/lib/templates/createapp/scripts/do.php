<?php

include dirname(__FILE__).'/../conf/boot.php';
require STATO_CORE_PATH.'/cli/cli.php';

SCommand::find_and_execute();
