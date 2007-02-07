<?php

define('STATO_ENV', 'test');

require(STATO_CORE_PATH.'/common/lib/initializer.php');

define('ST_DIR', STATO_CORE_PATH.'/vendor/simpletest');
require_once(ST_DIR.'/mock_objects.php');
require_once(ST_DIR.'/unit_tester.php');
require_once(ST_DIR.'/web_tester.php');
require_once(ST_DIR.'/reporter.php');

// we call session_start() now to avoid triggering "headers already sent" error
session_start();

class RunTestsCommand extends SCommand
{
    protected $allowed_options = array('path' => true);
    protected $allowed_params  = array('framework' => true);
   
    public function execute()
    {
        if (isset($this->options['path']))
            define('STATO_APP_ROOT_PATH', $this->options['path']);
            
        $framework = $this->params['framework'];
        $this->initialize($framework);
        $this->require_testing_classes();
        $test =& new GroupTest(ucfirst($framework).' tests');
        $this->add_framework_tests($test, $framework);
        
        $test->run(new TextReporter());
    }
    
    private function initialize($framework)
    {
        $config = new SConfiguration;
        if ($framework != 'cli' && $framework != 'common') $config->frameworks = array($framework);
        SInitializer::run($config);
    }
    
    private function require_testing_classes()
    {
        define('STATO_TESTING_PATH', STATO_CORE_PATH.'/cli/lib/testing');
        require_once(STATO_TESTING_PATH.'/show_passes.php');
        require_once(STATO_TESTING_PATH.'/color_text_reporter.php');
        require_once(STATO_TESTING_PATH.'/stato_test_case.php');
    }
    
    private function add_framework_tests($group_test, $framework)
    {
        $test_files = include(STATO_CORE_PATH."/{$framework}/{$framework}_test.php");
        set_include_path(get_include_path() . PATH_SEPARATOR . STATO_CORE_PATH."/{$framework}/test/");
        foreach ($test_files as $file) $group_test->addTestFile($file.'.test.php');
    }
}

?>
