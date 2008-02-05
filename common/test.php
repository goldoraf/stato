<?php

define('STATO_CORE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

require(STATO_CORE_PATH.'/common/common.php');
require(STATO_CORE_PATH.'/cli/cli.php');
require(STATO_CORE_PATH.'/vendor/simpletest/simpletest.php');

$test = new StatoGroupTest('Common classes tests');
$test->addTestFolder(STATO_CORE_PATH.'/common/test');
$test->run(new TextReporter());

?>
