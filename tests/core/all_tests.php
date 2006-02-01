<?php

define('APP_MODE', 'test');

define('DOC_ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../../../..')));
define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../../..')));
define('BASE_DIR', str_replace(DOC_ROOT_DIR, '', ROOT_DIR));

define('CORE_DIR', ROOT_DIR.'/core');

define('ST_DIR', ROOT_DIR.'/lib/simpletest');
require_once(ST_DIR.'/unit_tester.php');
require_once(ST_DIR.'/reporter.php');

define('TESTS_DIR', ROOT_DIR.'/core/tests');
require_once(TESTS_DIR.'/lib/showpasses.php');
require_once(TESTS_DIR.'/lib/activetestcase.php');

define('CONFIG_DIR', TESTS_DIR.'/core/conf');
define('FIXTURES_DIR', TESTS_DIR.'/core/fixtures');


require_once(CORE_DIR.'/common/common.php');

$test =& new GroupTest('All tests');

$test->addTestFile('callbacks.test.php');
$test->addTestFile('routes.test.php');
$test->addTestFile('validation.test.php');
$test->addTestFile('inflection.test.php');
$test->addTestFile('encryption.test.php');
$test->addTestFile('helpers.test.php');
$test->addTestFile('database.test.php');
$test->addTestFile('entity.test.php');
$test->addTestFile('activeentity.test.php');
//$test->addTestFile('mixins.test.php');
//$test->addTestFile('listmixin.test.php');
$test->addTestFile('activestore.test.php');
$test->addTestFile('eagerloading.test.php');
$test->addTestFile('associations.test.php');

$test->run(new HtmlReporter());

?>
