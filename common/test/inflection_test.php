<?php

class InflectionTest extends PHPUnit_Framework_TestCase
{
    function test_plural()
    {
        $this->assertEquals('products', SInflection::pluralize('product'));
    }
    
    function test_singular()
    {
        $this->assertEquals('product', SInflection::singularize('products'));
    }
    
    function test_underscore()
    {
        $this->assertEquals('my_test_controller', SInflection::underscore('MyTestController'));
        $this->assertEquals('s_my_test_controller', SInflection::underscore('SMyTestController'));
    }
    
    function test_camelize()
    {
        $this->assertEquals('MyTestController', SInflection::camelize('my_test_controller'));
        $this->assertEquals('SMyTestController', SInflection::camelize('s_my_test_controller'));
    }
    
    function test_dasherize()
    {
        $this->assertEquals('hello-world', SInflection::dasherize('hello_world'));
    }
    
    function test_wikify()
    {
        $this->assertEquals('hello_world', SInflection::wikify('Hello World'));
    }
    
    function test_humanize()
    {
        $this->assertEquals('Post', SInflection::humanize('post_id'));
        $this->assertEquals('Relative post', SInflection::humanize('relative_post_id'));
        $this->assertEquals('My test', SInflection::humanize('my_test'));
    }
}

?>
