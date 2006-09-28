<?php

define('STATO_TIME_START', microtime(true));

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

require(ROOT_DIR.'/core/common/common.php');
require(ROOT_DIR.'/core/controller/controller.php');
require(ROOT_DIR.'/core/model/model.php');
require(ROOT_DIR.'/core/view/view.php');
require(ROOT_DIR.'/core/webservice/webservice.php');

include(ROOT_DIR.'/conf/environment.php');

$controller = new SDispatcher();
$controller->dispatch();

?>
