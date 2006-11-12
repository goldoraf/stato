<?php

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../..')));

define('CORE_DIR', ROOT_DIR.'/core');
define('APP_DIR', ROOT_DIR.'/app');
require_once(CORE_DIR.'/common/common.php');
require_once(CORE_DIR.'/cli/cli.php');
require_once(CORE_DIR.'/model/model.php');

include(ROOT_DIR.'/conf/environment.php');

$options = SConsoleUtils::read_options('v:', array('version='));
if (isset($options['version'])) $version = $options['version'];
elseif (isset($options['v'])) $version = $options['v'];
else $version = null;

SMigrator::migrate(ROOT_DIR.'/db/migrate', $version);

?>
