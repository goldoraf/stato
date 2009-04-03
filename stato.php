<?php

define('STATO_CORE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__))));

require STATO_CORE_PATH.'/common/common.php';
require STATO_CORE_PATH.'/cli/cli.php';

SCommand::find_and_execute();
