<?php

define('ST_DIR', STATO_CORE_PATH.'/vendor/simpletest');
require_once(ST_DIR.'/mock_objects.php');
require_once(ST_DIR.'/unit_tester.php');
require_once(ST_DIR.'/web_tester.php');
require_once(ST_DIR.'/reporter.php');

define('STATO_TESTING_PATH', STATO_CORE_PATH.'/cli/lib/testing');
require_once(STATO_TESTING_PATH.'/show_passes.php');
require_once(STATO_TESTING_PATH.'/color_text_reporter.php');
require_once(STATO_TESTING_PATH.'/stato_test_case.php');
require_once(STATO_TESTING_PATH.'/active_test_case.php');
require_once(STATO_TESTING_PATH.'/controller_test_case.php');
require_once(STATO_TESTING_PATH.'/controller_mocks.php');

// we call session_start() now to avoid triggering "headers already sent" error
session_start();

SRoutes::initialize(include(STATO_APP_ROOT_PATH.'/conf/routes.php'));

class RunAppTestsCommand extends SCommand
{
    protected $allowed_params  = array('test_file' => true);
   
    public function execute()
    {
        set_include_path(get_include_path() . PATH_SEPARATOR . STATO_APP_PATH.'/controllers/'
                                            . PATH_SEPARATOR . STATO_APP_PATH.'/views/');
        
        if (in_array($this->params['test_file'], array('functional', 'unit')))
        {
            $test =& new GroupTest(ucfirst($this->params['test_file']).' tests');
            $this->add_test_files($test, $this->params['test_file']);
        }
        else
        {
            $file_path = STATO_APP_ROOT_PATH.'/test/'.$this->params['test_file'];
            if (!file_exists($file_path))
                throw new SConsoleException("Test file not found");
                
            require($file_path);
            $class_name = SInflection::camelize(basename($this->params['test_file'], '.php'));
            $test = new $class_name();
        }
        
        $test->run(new TextReporter());
    }
    
    private function add_test_files($group_test, $dir)
    {
        $test_files = SDir::entries(STATO_APP_ROOT_PATH."/test/$dir");
        set_include_path(get_include_path() . PATH_SEPARATOR . STATO_APP_ROOT_PATH."/test/$dir");
        foreach ($test_files as $file) $group_test->addTestFile($file);
    }
}

?>
