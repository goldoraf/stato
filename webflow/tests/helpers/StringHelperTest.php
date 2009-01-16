<?php

require_once dirname(__FILE__) . '/../../../tests/TestsHelper.php';

require_once 'helpers/string.php';

class Stato_StringHelperTest extends PHPUnit_Framework_TestCase
{
    public function testUnderscore()
    {
        $this->assertEquals('my_test_controller', underscore('MyTestController'));
        $this->assertEquals('s_my_test_controller', underscore('SMyTestController'));
    }
    
    public function testCamelize()
    {
        $this->assertEquals('MyTestController', camelize('my_test_controller'));
        $this->assertEquals('SMyTestController', camelize('s_my_test_controller'));
    }
    
    public function testDasherize()
    {
        $this->assertEquals('hello-world', dasherize('hello_world'));
    }
    
    public function testWikify()
    {
        $this->assertEquals('hello_world', wikify('Hello World'));
    }
    
    public function testHumanize()
    {
        $this->assertEquals('Post', humanize('post_id'));
        $this->assertEquals('Relative post', humanize('relative_post_id'));
        $this->assertEquals('My test', humanize('my_test'));
    }
    
    public function testUrlize()
    {
        $this->assertEquals('ma-premiere-ligne-de-code', urlize('Ma première ligne de code'));
    }
    
    public function testSanitizeFilename()
    {
        $this->assertEquals('compte_rendu_reunion.doc', sanitize_filename('Compte rendu réunion.doc'));
    }
}
