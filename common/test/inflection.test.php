<?php

class InflectionTest extends UnitTestCase
{
    function testPlural()
    {
        $this->assertEqual('products', SInflection::pluralize('product'));
    }
    
    function testSingular()
    {
        $this->assertEqual('product', SInflection::singularize('products'));
    }
    
    function testUnderscore()
    {
        $this->assertEqual('my_test_controller', SInflection::underscore('MyTestController'));
        $this->assertEqual('s_my_test_controller', SInflection::underscore('SMyTestController'));
    }
    
    function testCamelize()
    {
        $this->assertEqual('MyTestController', SInflection::camelize('my_test_controller'));
        $this->assertEqual('SMyTestController', SInflection::camelize('s_my_test_controller'));
    }
    
    function testWikify()
    {
        $this->assertEqual('hello_world', SInflection::wikify('Hello World'));
    }
    
    function testHumanize()
    {
        $this->assertEqual('Post', SInflection::humanize('post_id'));
        $this->assertEqual('Relative post', SInflection::humanize('relative_post_id'));
        $this->assertEqual('My test', SInflection::humanize('my_test'));
    }
}

?>
