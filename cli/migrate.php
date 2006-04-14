<?php

define('APP_MODE', 'dev');

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../..')));

define('CORE_DIR', ROOT_DIR.'/core');
require_once(CORE_DIR.'/common/common.php');
require_once(CORE_DIR.'/cli/cli.php');
require_once(CORE_DIR.'/model/model.php');

$options = SConsoleUtils::readOptions('v:', array('version='));
if (isset($options['version'])) $version = $options['version'];
elseif (isset($options['v'])) $version = $options['v'];
else $version = null;

SMigrator::migrate(ROOT_DIR.'/db/migrate', $version);

?>
