<?php

define('STATO_CORE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

require STATO_CORE_PATH.'/common/common.php';
require STATO_CORE_PATH.'/components/console/console.php';
require STATO_CORE_PATH.'/gemini/gemini.php';
require STATO_CORE_PATH.'/vendor/simpletest/simpletest.php';

$test = new StatoGroupTest('Gemini tests');
$test->addTestFolder(STATO_CORE_PATH.'/gemini/test');
$test->addTestFolder(STATO_CORE_PATH.'/gemini/test/helpers');
$test->run(new TextReporter());

?>
