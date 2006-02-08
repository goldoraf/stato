<?php

define('APP_MODE', 'dev');

define('DOC_ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));
define('ROOT_DIR', str_replace('\\', '/', dirname(__FILE__)));
define('BASE_DIR', str_replace(DOC_ROOT_DIR, '', ROOT_DIR));
define('APP_DIR', ROOT_DIR.'/app');

define('SITE_HOST', $_SERVER['HTTP_HOST']);
define('SITE_URL', 'http://'.SITE_HOST.BASE_DIR);

/*echo 'DOC_ROOT : '.DOC_ROOT_DIR."\n"
.'ROOT_DIR : '.ROOT_DIR."\n"
.'BASE_DIR : '.BASE_DIR."\n"
.'SITE_HOST : '.SITE_HOST."\n"
.'SITE_URL : '.SITE_URL;*/

require_once('core/common/common.php');
require_once('core/controller/controller.php');

$controller = new SDispatcher();
$controller->dispatch();

?>
