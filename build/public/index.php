<?php

define('APP_MODE', 'dev');
define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

require_once('../core/controller/controller.php');
require_once('../core/common/common.php');

$controller = new SDispatcher();
$controller->dispatch();

?>
