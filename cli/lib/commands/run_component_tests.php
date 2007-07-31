<?php

define('STATO_APP_ROOT_PATH', 'dummy');

require(STATO_CORE_PATH.'/common/lib/initializer.php');

define('ST_DIR', STATO_CORE_PATH.'/vendor/simpletest');
require_once(ST_DIR.'/mock_objects.php');
require_once(ST_DIR.'/unit_tester.php');
require_once(ST_DIR.'/web_tester.php');
require_once(ST_DIR.'/reporter.php');

class RunComponentTestsCommand extends SCommand
{
    protected $allowed_params  = array('component' => true);
   
    public function execute()
    {   
        $component = $this->params['component'];
        $this->initialize($component);
        $this->require_testing_classes();
        $test = new GroupTest(ucfirst($component).' tests');
        $this->add_component_tests($test, $component);
        
        $test->run(new TextReporter());
    }
    
    private function initialize($component)
    {
        SDependencies::require_component($component);
    }
    
    private function require_testing_classes()
    {
        define('STATO_TESTING_PATH', STATO_CORE_PATH.'/cli/lib/testing');
        require_once(STATO_TESTING_PATH.'/show_passes.php');
        require_once(STATO_TESTING_PATH.'/color_text_reporter.php');
        require_once(STATO_TESTING_PATH.'/stato_test_case.php');
    }
    
    private function add_component_tests($group_test, $component)
    {
        $path = STATO_CORE_PATH."/components/{$component}/{$component}_test.php";
        if (!file_exists($path))
            throw new SConsoleException("There is no tests defined for $component component");
            
        $test_files = include($path);
        set_include_path(get_include_path() . PATH_SEPARATOR . STATO_CORE_PATH."/components/{$component}/test/");
        foreach ($test_files as $file) $group_test->addTestFile($file.'.test.php');
    }
}

?>
