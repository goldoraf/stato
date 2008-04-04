<?php

class InflectionTest extends UnitTestCase
{
    public function test_plural()
    {
        $this->assertEqual('products', SInflection::pluralize('product'));
    }
    
    public function test_singular()
    {
        $this->assertEqual('product', SInflection::singularize('products'));
    }
    
    public function test_underscore()
    {
        $this->assertEqual('my_test_controller', SInflection::underscore('MyTestController'));
        $this->assertEqual('s_my_test_controller', SInflection::underscore('SMyTestController'));
    }
    
    public function test_camelize()
    {
        $this->assertEqual('MyTestController', SInflection::camelize('my_test_controller'));
        $this->assertEqual('SMyTestController', SInflection::camelize('s_my_test_controller'));
    }
    
    public function test_dasherize()
    {
        $this->assertEqual('hello-world', SInflection::dasherize('hello_world'));
    }
    
    public function test_wikify()
    {
        $this->assertEqual('hello_world', SInflection::wikify('Hello World'));
    }
    
    public function test_humanize()
    {
        $this->assertEqual('Post', SInflection::humanize('post_id'));
        $this->assertEqual('Relative post', SInflection::humanize('relative_post_id'));
        $this->assertEqual('My test', SInflection::humanize('my_test'));
    }
    
    public function test_urlize()
    {
        $this->assertEqual('ma-premiere-ligne-de-code', SInflection::urlize('Ma première ligne de code'));
    }
    
    public function test_sanitize_filename()
    {
        $this->assertEqual('compte_rendu_reunion.doc', SInflection::sanitize_filename('Compte rendu réunion.doc'));
    }
}

?>
