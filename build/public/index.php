<?php

define('STATO_TIME_START', microtime(true));

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

include(ROOT_DIR.'/conf/environment.php');

require(CORE_DIR.'/common/common.php');
require(CORE_DIR.'/controller/controller.php');
require(CORE_DIR.'/model/model.php');
require(CORE_DIR.'/view/view.php');
require(CORE_DIR.'/webservice/webservice.php');
require(CORE_DIR.'/mailer/mailer.php');

$controller = new SDispatcher();
$controller->dispatch();

?>
