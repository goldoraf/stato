<?php

define('APP_MODE', 'test');

define('ROOT_DIR', str_replace('\\', '/', realpath(dirname(__FILE__).'/../..')));

define('CORE_DIR', ROOT_DIR.'/core');
require_once(CORE_DIR.'/common/common.php');

define('ST_DIR', ROOT_DIR.'/lib/simpletest');
require_once(ST_DIR.'/mock_objects.php');
require_once(ST_DIR.'/unit_tester.php');
require_once(ST_DIR.'/reporter.php');

define('TESTS_DIR', CORE_DIR.'/cli/testing');
require_once(TESTS_DIR.'/showpasses.php');
require_once(TESTS_DIR.'/colortextreporter.php');
require_once(TESTS_DIR.'/activetestcase.php');
require_once(TESTS_DIR.'/helpertestcase.php');

// we call session_start() now to avoid triggering "headers already sent" error
session_start();

function addPackageTests($groupTest, $package)
{
    $testFiles = include(CORE_DIR."/{$package}/{$package}_test.php");
    set_include_path(get_include_path() . PATH_SEPARATOR . CORE_DIR."/{$package}/test/");
    foreach ($testFiles as $file) $groupTest->addTestFile($file.'.test.php');
}

if ($_SERVER['argc'] == 2)
{
    if (strpos($_SERVER['argv'][1], '/') !== false)
    {
        list($package, $file) = explode('/', $_SERVER['argv'][1]);
        $class = ucfirst(str_replace('_', '', $file)).'Test';
        require_once(CORE_DIR."/{$package}/test/{$file}.test.php");
        $test = new $class();
    }
    else
    {
        $package = $_SERVER['argv'][1];
        $test =& new GroupTest(ucfirst($package).' tests');
        addPackageTests($test, $package);
    }
}
else
{
    /*$test =& new GroupTest('All tests');
    foreach (array('common', 'controller', 'model', 'view') as $package) addPackageTests($test, $package);*/
    echo "\nPHP is not Ruby ! Please launch tests separately for each layer to avoid 
conflicts between fake and normal classes.\n
    Usage : run_tests.php [layer]\n
    Layers :\n
    - common\n
    - controller\n
    - model\n
    - view\n";
    die();
}

$test->run(new TextReporter());

?>
