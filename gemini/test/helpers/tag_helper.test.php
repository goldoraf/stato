<?php

class TagHelperTest extends StatoTestCase
{   
    public function test_tags()
    {
        $this->assertDomEqual(tag('fake', array('key'=>'value')), '<fake key="value" />');
        $this->assertDomEqual(
            content_tag('a', 'Test', array('class'=>'link', 'href'=>'http://www.php.net')), 
            '<a class="link" href="http://www.php.net">Test</a>'
        );
    }
    
    public function test_reject_options()
    {
        $this->assertDomEqual(tag('p', array('ignore' => null)), '<p />');
        $this->assertDomEqual(tag('p', array('ignore' => false)), '<p />');
    }
    
    public function test_convert_true_options()
    {
        $this->assertDomEqual(
            tag('p', array('disabled' => true, 'multiple' => true)),
            '<p disabled="disabled" multiple="multiple" />'
        );
    }
    
    public function test_cdata_section()
    {
        $this->assertEqual(cdata_section('<hello world>'), '<![CDATA[<hello world>]]>');
    }
}

?>
