<?php

date_default_timezone_set('Europe/Paris');
setlocale(LC_TIME, 'en_EN.utf8', 'en_EN', 'en');

define('STATO_CORE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

require STATO_CORE_PATH.'/common/common.php';
require STATO_CORE_PATH.'/components/console/console.php';
require STATO_CORE_PATH.'/mercury/mercury.php';
require STATO_CORE_PATH.'/components/test/test.php';

define('STATO_TESTING_ADAPTER', 'mysql');
define('STATO_FIXTURES_DIR', STATO_CORE_PATH.'/mercury/test/fixtures');
require_once STATO_FIXTURES_DIR.'/models.php';

if (!file_exists(STATO_CORE_PATH.'/mercury/test/connections/'.STATO_TESTING_ADAPTER.'.php'))
    throw new Exception(STATO_TESTING_ADAPTER.' adapter not found');

require_once STATO_CORE_PATH.'/mercury/test/connections/'.STATO_TESTING_ADAPTER.'.php';

$test = new StatoGroupTest('Mercury tests');
$test->addTestFolder(STATO_CORE_PATH.'/mercury/test');
$test->run(new TextReporter());

?>
