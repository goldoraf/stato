<?php

class InflectionTest extends UnitTestCase
{
    function test_plural()
    {
        $this->assertEqual('products', SInflection::pluralize('product'));
    }
    
    function test_singular()
    {
        $this->assertEqual('product', SInflection::singularize('products'));
    }
    
    function test_underscore()
    {
        $this->assertEqual('my_test_controller', SInflection::underscore('MyTestController'));
        $this->assertEqual('s_my_test_controller', SInflection::underscore('SMyTestController'));
    }
    
    function test_camelize()
    {
        $this->assertEqual('MyTestController', SInflection::camelize('my_test_controller'));
        $this->assertEqual('SMyTestController', SInflection::camelize('s_my_test_controller'));
    }
    
    function test_dasherize()
    {
        $this->assertEqual('hello-world', SInflection::dasherize('hello_world'));
    }
    
    function test_wikify()
    {
        $this->assertEqual('hello_world', SInflection::wikify('Hello World'));
    }
    
    function test_humanize()
    {
        $this->assertEqual('Post', SInflection::humanize('post_id'));
        $this->assertEqual('Relative post', SInflection::humanize('relative_post_id'));
        $this->assertEqual('My test', SInflection::humanize('my_test'));
    }
}

?>
