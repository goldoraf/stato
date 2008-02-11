<?php

class FormTagHelperTest extends StatoTestCase
{
    public function test_checkbox_tag()
    {
        $this->assertDomEqual(
            check_box_tag('admin', 1),
            '<input id="admin" name="admin" type="checkbox" value="1" />'
        );
    }
    
    public function test_hidden_field_tag()
    {
        $this->assertDomEqual(
            hidden_field_tag('id', 3),
            '<input id="id" name="id" type="hidden" value="3" />'
        );
    }
    
    public function test_password_field_tag()
    {
        $this->assertDomEqual(
            password_field_tag('password'),
            '<input id="password" name="password" type="password" />'
        );
    }
    
    public function test_radio_button_tag()
    {
        $this->assertDomEqual(
            radio_button_tag('people', 'raph'),
            '<input id="people" name="people" type="radio" value="raph" />'
        );
    }
    
    public function test_select_tag()
    {
        $this->assertDomEqual(
            select_tag('people', '<option>raph</option>'),
            '<select id="people" name="people"><option>raph</option></select>'
        );
    }
    
    public function test_text_area_tag_with_size()
    {
        $this->assertDomEqual(
            text_area_tag('body', 'hello world', array('size' => '20x40')),
            '<textarea cols="20" id="body" name="body" rows="40">hello world</textarea>'
        );
    }
    
    public function test_text_field_tag()
    {
        $this->assertDomEqual(
            text_field_tag('title', 'Hello !'),
            '<input id="title" name="title" type="text" value="Hello !" />'
        );
    }
    
    public function test_text_field_tag_with_class_option()
    {
        $this->assertDomEqual(
            text_field_tag('title', 'Hello !', array('class' => 'admin')),
            '<input class="admin" id="title" name="title" type="text" value="Hello !" />'
        );
    }
    
    public function test_boolean_options()
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
    
    public function test_submit_tag()
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
