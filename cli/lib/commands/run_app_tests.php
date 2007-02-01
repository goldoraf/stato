<?php

define('ST_DIR', STATO_CORE_PATH.'/vendor/simpletest');
require_once(ST_DIR.'/mock_objects.php');
require_once(ST_DIR.'/unit_tester.php');
require_once(ST_DIR.'/reporter.php');

define('STATO_TESTING_PATH', STATO_CORE_PATH.'/cli/lib/testing');
require_once(STATO_TESTING_PATH.'/show_passes.php');
require_once(STATO_TESTING_PATH.'/color_text_reporter.php');
require_once(STATO_TESTING_PATH.'/stato_test_case.php');

// we call session_start() now to avoid triggering "headers already sent" error
session_start();

class RunAppTestsCommand extends SCommand
{
    protected $allowed_params  = array('test_file' => false);
   
    public function execute()
    {
        if (isset($this->params['test_file']))
        {
            $file_path = STATO_APP_ROOT_PATH.'/test/'.$this->params['test_file'];
            if (!file_exists($file_path))
                throw new SConsoleException("Test file not found");
                
            require($file_path);
            $class_name = SInflection::camelize(basename($this->params['test_file'], '.php'));
            $test = new $class_name();
        }
        else
        {
            $test =& new GroupTest('Application tests');
            $this->add_test_files($test);
        }
        
        $test->run(new TextReporter());
    }
    
    private function add_test_files($group_test)
    {
        $test_files = SDir::entries(STATO_APP_ROOT_PATH.'/test');
        set_include_path(get_include_path() . PATH_SEPARATOR . STATO_APP_ROOT_PATH.'/test/');
        foreach ($test_files as $file) $group_test->addTestFile($file);
    }
}

?>
