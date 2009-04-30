<?php

namespace Stato\Webflow\Helper;

use Stato\Webflow\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

require_once 'Stato/Webflow/Helper/String.php';

class StringTest extends TestCase
{
    public function testHtmlEscape()
    {
        $this->assertEquals('&lt;a href=&quot;dummy.com&quot;&gt;test&lt;/a&gt;',
                            html_escape('<a href="dummy.com">test</a>'));   
    }
    
    public function testJsEscape()
    {
        $str = "It\'s just a \\\"dummy\\\" sentence\\\\n";
        $this->assertEquals($str, js_escape("It's just a \"dummy\" sentence\n"));
    }
    
    public function testTruncate()
    {
        $this->assertEquals('test', truncate('test'));
        $this->assertEquals('Lorem ipsum dolor sit amet,...', 
                            truncate('Lorem ipsum dolor sit amet, consectetur adipiscing elit.'));
    }
    
    public function testCycle()
    {
        $this->assertEquals('even', cycle(array('even', 'odd')));
        $this->assertEquals('odd', cycle(array('even', 'odd')));
        $this->assertEquals('even', cycle(array('even', 'odd')));
    }
    
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
