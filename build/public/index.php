<?php

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

require_once(ROOT_DIR.'/core/common/common.php');
require_once(ROOT_DIR.'/core/controller/controller.php');
require_once(ROOT_DIR.'/core/model/model.php');
require_once(ROOT_DIR.'/core/view/view.php');
require_once(ROOT_DIR.'/core/webservice/webservice.php');

include(ROOT_DIR.'/conf/environment.php');

$controller = new SDispatcher();
$controller->dispatch();

?>
