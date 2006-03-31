<?php

require_once(CORE_DIR.'/view/view.php');

class TagHelperTest extends HelperTestCase
{   
    public function testTags()
    {
        $this->assertDomEqual(tag('fake', array('key'=>'value')), '<fake key="value" />');
        $this->assertDomEqual(
            content_tag('a', 'Test', array('class'=>'link', 'href'=>'http://www.php.net')), 
            '<a class="link" href="http://www.php.net">Test</a>'
        );
    }
}

?>
