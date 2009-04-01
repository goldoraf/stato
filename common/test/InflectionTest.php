<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

class InflectionTest extends PHPUnit_Framework_TestCase
{
    public function test_plural()
    {
        $this->assertEquals('products', SInflection::pluralize('product'));
    }
    
    public function test_singular()
    {
        $this->assertEquals('product', SInflection::singularize('products'));
    }
    
    public function test_underscore()
    {
        $this->assertEquals('my_test_controller', SInflection::underscore('MyTestController'));
        $this->assertEquals('s_my_test_controller', SInflection::underscore('SMyTestController'));
    }
    
    public function test_camelize()
    {
        $this->assertEquals('MyTestController', SInflection::camelize('my_test_controller'));
        $this->assertEquals('SMyTestController', SInflection::camelize('s_my_test_controller'));
    }
    
    public function test_dasherize()
    {
        $this->assertEquals('hello-world', SInflection::dasherize('hello_world'));
    }
    
    public function test_wikify()
    {
        $this->assertEquals('hello_world', SInflection::wikify('Hello World'));
    }
    
    public function test_humanize()
    {
        $this->assertEquals('Post', SInflection::humanize('post_id'));
        $this->assertEquals('Relative post', SInflection::humanize('relative_post_id'));
        $this->assertEquals('My test', SInflection::humanize('my_test'));
    }
    
    public function test_urlize()
    {
        $this->assertEquals('ma-premiere-ligne-de-code', SInflection::urlize('Ma première ligne de code'));
    }
    
    public function test_sanitize_filename()
    {
        $this->assertEquals('compte_rendu_reunion.doc', SInflection::sanitize_filename('Compte rendu réunion.doc'));
    }
}

