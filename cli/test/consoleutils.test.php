<?php

require_once(CORE_DIR.'/cli/cli.php');

class ConsoleUtilsTest extends UnitTestCase
{
    public function test_parsing_long_options()
    {
        list($options, $params) = SConsoleUtils::get_options_and_params(
            array('--abc', '--def=test'),
            array('abc' => false, 'def' => true),
            array()
        );
        $this->assertEqual(array('abc' => true, 'def' => 'test'), $options);
    }
    
    public function test_parsing_short_options()
    {
        list($options, $params) = SConsoleUtils::get_options_and_params(
            array('-a', '-d', 'test'),
            array('abc' => false, 'def' => true),
            array()
        );
        $this->assertEqual(array('abc' => true, 'def' => 'test'), $options);
    }
    
    public function test_parsing_long_options_mixed_with_params()
    {
        list($options, $params) = SConsoleUtils::get_options_and_params(
            array('--abc', 'project', '--def=test', 'www'),
            array('abc' => false, 'def' => true),
            array('action' => true, 'folder' => false)
        );
        $this->assertEqual(array('abc' => true, 'def' => 'test'), $options);
        $this->assertEqual(array('action' => 'project', 'folder' => 'www'), $params);
    }
    
    public function test_parsing_short_options_mixed_with_params()
    {
        list($options, $params) = SConsoleUtils::get_options_and_params(
            array('project', '-a', '-d', 'test', 'www'),
            array('abc' => false, 'def' => true),
            array('action' => true, 'folder' => false)
        );
        $this->assertEqual(array('abc' => true, 'def' => 'test'), $options);
        $this->assertEqual(array('action' => 'project', 'folder' => 'www'), $params);
    }
    
    public function test_parsing_mixed()
    {
        list($options, $params) = SConsoleUtils::get_options_and_params(
            array('project', '-a', '--def=test', 'test', '-x', 'hello', 'www'),
            array('abc' => false, 'def' => true, 'xyz' => true),
            array('action' => true, 'type' => true, 'folder' => false)
        );
        $this->assertEqual(array('abc' => true, 'def' => 'test', 'xyz' => 'hello'), $options);
        $this->assertEqual(array('action' => 'project', 'type' => 'test', 'folder' => 'www'), $params);
    }
}

?>
