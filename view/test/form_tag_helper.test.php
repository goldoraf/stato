<?php

require_once(CORE_DIR.'/view/view.php');

if (!class_exists('SUrlRewriter'))
{
    class SUrlRewriter
    {
        public static function urlFor($options)
        {
            return 'http://www.example.com';
        }
    }
}

class FormTagHelperTest extends HelperTestCase
{
    public function testFormTag()
    {
        $this->assertDomEqual(
            form_tag('http://www.example.com').end_form_tag(),
            '<form action="http://www.example.com" method="post"></form>'
        );
        $this->assertDomEqual(
            form_tag('http://www.example.com', array('multipart' => true)).end_form_tag(),
            '<form action="http://www.example.com" enctype="multipart/form-data" method="post"></form>'
        );
    }
    
    public function testCheckboxTag()
    {
        $this->assertDomEqual(
            check_box_tag('admin', 1),
            '<input id="admin" name="admin" type="checkbox" value="1" />'
        );
    }
    
    public function testHiddenFieldTag()
    {
        $this->assertDomEqual(
            hidden_field_tag('id', 3),
            '<input id="id" name="id" type="hidden" value="3" />'
        );
    }
    
    public function testPasswordFieldTag()
    {
        $this->assertDomEqual(
            password_field_tag('password'),
            '<input id="password" name="password" type="password" />'
        );
    }
    
    public function testRadioButtonTag()
    {
        $this->assertDomEqual(
            radio_button_tag('people', 'raph'),
            '<input id="people" name="people" type="radio" value="raph" />'
        );
    }
    
    public function testSelectTag()
    {
        $this->assertDomEqual(
            select_tag('people', '<option>raph</option>'),
            '<select id="people" name="people"><option>raph</option></select>'
        );
    }
    
    public function testTextAreaTagWithSize()
    {
        $this->assertDomEqual(
            text_area_tag('body', 'hello world', array('size' => '20x40')),
            '<textarea cols="20" id="body" name="body" rows="40">hello world</textarea>'
        );
    }
    
    public function testTextFieldTag()
    {
        $this->assertDomEqual(
            text_field_tag('title', 'Hello !'),
            '<input id="title" name="title" type="text" value="Hello !" />'
        );
    }
    
    public function testTextFieldTagWithClassOption()
    {
        $this->assertDomEqual(
            text_field_tag('title', 'Hello !', array('class' => 'admin')),
            '<input class="admin" id="title" name="title" type="text" value="Hello !" />'
        );
    }
    
    public function testBooleanOptions()
    {
        $this->assertDomEqual(
            check_box_tag('admin', 1, true, array('disabled' => true, 'readonly' => true)),
            '<input checked="checked" disabled="disabled" id="admin" name="admin" readonly="readonly" type="checkbox" value="1" />'
        );
        $this->assertDomEqual(
            check_box_tag('admin', 1, true, array('disabled' => false, 'readonly' => null)),
            '<input checked="checked" id="admin" name="admin" type="checkbox" value="1" />'
        );
        $this->assertDomEqual(
            select_tag('people', '<option>raph</option>', array('multiple' => true)),
            '<select id="people" name="people" multiple="multiple"><option>raph</option></select>'
        );
        $this->assertDomEqual(
            select_tag('people', '<option>raph</option>', array('multiple' => null)),
            '<select id="people" name="people"><option>raph</option></select>'
        );
    }
    
    public function testSubmitTag()
    {
        $this->assertDomEqual(
            submit_tag('Save'),
            '<input name="commit" type="submit" value="Save" />'
        );
        $this->assertDomEqual(
            submit_tag('Save', array('disable_with' => 'Saving...', 'onclick' => 'alert(\'hello !\')')),
            '<input name="commit" type="submit" value="Save" 
            onclick="this.disabled=true;this.value=\'Saving...\';this.form.submit();alert(\'hello !\')" />'
        );
    }
}

?>
