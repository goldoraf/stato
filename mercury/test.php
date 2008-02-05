<?php

define('STATO_CORE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

require(STATO_CORE_PATH.'/common/common.php');
require(STATO_CORE_PATH.'/cli/cli.php');
require(STATO_CORE_PATH.'/mercury/mercury.php');
require(STATO_CORE_PATH.'/vendor/simpletest/simpletest.php');

define('STATO_TESTING_ADAPTER', 'mysql');
define('STATO_FIXTURES_DIR', STATO_CORE_PATH.'/mercury/test/fixtures');
require_once(STATO_FIXTURES_DIR.'/models.php');

if (!file_exists(STATO_CORE_PATH.'/mercury/test/connections/'.STATO_TESTING_ADAPTER.'.php'))
    throw new Exception(STATO_TESTING_ADAPTER.' adapter not found');

require_once(STATO_CORE_PATH.'/mercury/test/connections/'.STATO_TESTING_ADAPTER.'.php');

$test = new StatoGroupTest('Mercury tests');
$test->addTestFolder(STATO_CORE_PATH.'/mercury/test');
$test->run(new TextReporter());

?>
