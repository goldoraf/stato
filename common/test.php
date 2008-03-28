<?php

if (!defined('STATO_CORE_PATH'))
    define('STATO_CORE_PATH', str_replace('\\', '/', realpath(dirname(__FILE__).'/..')));

require STATO_CORE_PATH.'/common/common.php';
require STATO_CORE_PATH.'/components/console/console.php';

require STATO_CORE_PATH.'/test/stato_test_suite.php';

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
 
$suite = new StatoTestSuite();
$suite->addTestDirectory(STATO_CORE_PATH.'/common/test');
$suite->run();

?>
