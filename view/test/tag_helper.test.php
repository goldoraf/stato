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
    
    public function testRejectOptions()
    {
        $this->assertDomEqual(tag('p', array('ignore' => null)), '<p />');
        $this->assertDomEqual(tag('p', array('ignore' => false)), '<p />');
    }
    
    public function testConvertTrueOptions()
    {
        $this->assertDomEqual(
            tag('p', array('disabled' => true, 'multiple' => true)),
            '<p disabled="disabled" multiple="multiple" />'
        );
    }
    
    public function testCdataSection()
    {
        $this->assertEqual(cdata_section('<hello world>'), '<![CDATA[<hello world>]]>');
    }
}

?>
