<?php

define('DEBUG_MODE', True);

define('ROOT_DIR', str_replace('\\', '/', dirname(__FILE__)));
define('BASE_DIR', str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_DIR));
define('APP_DIR', ROOT_DIR.'/app');

define('SITE_HOST', $_SERVER['HTTP_HOST']);
define('SITE_URL', 'http://'.SITE_HOST.BASE_DIR);

require_once('core/common/common.php');
require_once('core/controller/controller.php');

$controller = new Dispatcher();
$controller->dispatch();

?>
