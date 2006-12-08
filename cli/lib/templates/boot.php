// Dont't change this file. Configuration is done in conf/environment.php

define('STATO_TIME_START', microtime(true));
define('STATO_CORE_PATH', '<?php echo STATO_CORE_PATH; ?>');
define('STATO_APP_ROOT_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

include(STATO_APP_ROOT_PATH.'/conf/environment.php');

require(STATO_CORE_PATH.'/common/common.php');
require(STATO_CORE_PATH.'/cli/cli.php');
require(STATO_CORE_PATH.'/controller/controller.php');
require(STATO_CORE_PATH.'/model/model.php');
require(STATO_CORE_PATH.'/view/view.php');
require(STATO_CORE_PATH.'/webservice/webservice.php');
require(STATO_CORE_PATH.'/mailer/mailer.php');
