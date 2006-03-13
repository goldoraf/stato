<?php

require_once(CORE_DIR.'/view/view.php');

class HelpersTest extends UnitTestCase
{
    function testTags()
    {
        $tag = content_tag('a', 'Test', array('class'=>'link', 'href'=>'http://www.php.net'));
        $this->assertEqual($tag, '<a class="link" href="http://www.php.net">Test</a>');
        $this->assertEqual(tag('fake', array('key'=>'value')), '<fake key="value" />');
    }
    
    function testAjax()
    {
        $link = link_to_function('Hello', "alert('Hello World')");
        $this->assertEqual($link, '<a href="#" onclick="alert(\'Hello World\'); return false;">Hello</a>');
    }
}

?>
