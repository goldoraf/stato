<?php

define('APP_MODE', 'test');

define('DOC_ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../../../..')));
define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../../..')));
define('BASE_DIR', str_replace(DOC_ROOT_DIR, '', ROOT_DIR));

define('CORE_DIR', ROOT_DIR.'/core');
require_once(CORE_DIR.'/common/common.php');

define('ST_DIR', ROOT_DIR.'/lib/simpletest');
require_once(ST_DIR.'/mock_objects.php');
require_once(ST_DIR.'/unit_tester.php');
require_once(ST_DIR.'/reporter.php');

define('TESTS_DIR', ROOT_DIR.'/core/tests');
require_once(TESTS_DIR.'/lib/showpasses.php');
require_once(TESTS_DIR.'/lib/colortextreporter.php');
require_once(TESTS_DIR.'/lib/activetestcase.php');

define('CONFIG_DIR', TESTS_DIR.'/core/conf');
define('FIXTURES_DIR', TESTS_DIR.'/core/fixtures');

set_include_path(get_include_path() . PATH_SEPARATOR . TESTS_DIR.'/core');

// we call session_start() now to avoid triggering "headers already sent" error
session_start();

if ($_SERVER['argc'] != 1)
{
    $file = $_SERVER['argv'][1].'.test.php';
    $class = ucfirst($_SERVER['argv'][1]).'Test';
    require_once($file);
    $test = new $class();
}
else
{
    $test =& new GroupTest('All tests');

    $test->addTestFile('filters.test.php');
    $test->addTestFile('callbacks.test.php');
    $test->addTestFile('routes.test.php');
    $test->addTestFile('inflection.test.php');
    $test->addTestFile('encryption.test.php');
    $test->addTestFile('helpers.test.php');
    $test->addTestFile('database.test.php');
    $test->addTestFile('record.test.php');
    $test->addTestFile('activerecord.test.php');
    //$test->addTestFile('mixins.test.php');
    //$test->addTestFile('listmixin.test.php');
    $test->addTestFile('activestore.test.php');
    $test->addTestFile('eagerloading.test.php');
    $test->addTestFile('associations.test.php');
    $test->addTestFile('decorators.test.php');
    $test->addTestFile('listdecorator.test.php');
}

$test->run(new TextReporter());

?>
