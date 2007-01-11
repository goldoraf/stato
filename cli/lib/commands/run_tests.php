<?php

define('STATO_APP_MODE', 'test');

define('ST_DIR', STATO_CORE_PATH.'/vendor/simpletest');
require_once(ST_DIR.'/mock_objects.php');
require_once(ST_DIR.'/unit_tester.php');
require_once(ST_DIR.'/reporter.php');

define('TESTS_DIR', STATO_CORE_PATH.'/cli/lib/testing');
require_once(TESTS_DIR.'/show_passes.php');
require_once(TESTS_DIR.'/color_text_reporter.php');
require_once(TESTS_DIR.'/stato_test_case.php');
require_once(TESTS_DIR.'/active_test_case.php');
require_once(TESTS_DIR.'/helper_test_case.php');
require_once(TESTS_DIR.'/controller_test_case.php');
require_once(TESTS_DIR.'/controller_mocks.php');

// we call session_start() now to avoid triggering "headers already sent" error
session_start();

class RunTestsCommand extends SCommand
{
    protected $allowed_options = array('path' => true);
    protected $allowed_params  = array('package' => true);
   
    public function execute()
    {
        if (isset($this->options['path']))
            define('STATO_APP_ROOT_PATH', $this->options['path']);
            
        if (strpos($this->params['package'], '/') !== false)
        {
            list($package, $file) = explode('/', $this->params['package']);
            $class = ucfirst(str_replace('_', '', $file)).'Test';
            require_once(STATO_CORE_PATH."/{$package}/test/{$file}.test.php");
            $test = new $class();
        }
        else
        {
            $package = $this->params['package'];
            $test =& new GroupTest(ucfirst($package).' tests');
            $this->add_package_tests($test, $package);
        }
        
        $test->run(new TextReporter());
    }
    
    private function add_package_tests($group_test, $package)
    {
        $test_files = include(STATO_CORE_PATH."/{$package}/{$package}_test.php");
        set_include_path(get_include_path() . PATH_SEPARATOR . STATO_CORE_PATH."/{$package}/test/");
        foreach ($test_files as $file) $group_test->addTestFile($file.'.test.php');
    }
}

?>
