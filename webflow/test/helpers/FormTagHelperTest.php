<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

class FormTagHelperTest extends StatoTestCase
{
    public function test_checkbox_tag()
    {
        $this->assertDomEquals(
            check_box_tag('admin', 1),
            '<input id="admin" name="admin" type="checkbox" value="1" />'
        );
    }
    
    public function test_hidden_field_tag()
    {
        $this->assertDomEquals(
            hidden_field_tag('id', 3),
            '<input id="id" name="id" type="hidden" value="3" />'
        );
    }
    
    public function test_password_field_tag()
    {
        $this->assertDomEquals(
            password_field_tag('password'),
            '<input id="password" name="password" type="password" />'
        );
    }
    
    public function test_radio_button_tag()
    {
        $this->assertDomEquals(
            radio_button_tag('people', 'raph'),
            '<input id="people" name="people" type="radio" value="raph" />'
        );
    }
    
    public function test_select_tag()
    {
        $this->assertDomEquals(
            select_tag('people', '<option>raph</option>'),
            '<select id="people" name="people"><option>raph</option></select>'
        );
    }
    
    public function test_text_area_tag_with_size()
    {
        $this->assertDomEquals(
            text_area_tag('body', 'hello world', array('size' => '20x40')),
            '<textarea cols="20" id="body" name="body" rows="40">hello world</textarea>'
        );
    }
    
    public function test_text_field_tag()
    {
        $this->assertDomEquals(
            text_field_tag('title', 'Hello !'),
            '<input id="title" name="title" type="text" value="Hello !" />'
        );
    }
    
    public function test_text_field_tag_with_class_option()
    {
        $this->assertDomEquals(
            text_field_tag('title', 'Hello !', array('class' => 'admin')),
            '<input class="admin" id="title" name="title" type="text" value="Hello !" />'
        );
    }
    
    public function test_boolean_options()
    {
        $this->assertDomEquals(
            check_box_tag('admin', 1, true, array('disabled' => true, 'readonly' => true)),
            '<input checked="checked" disabled="disabled" id="admin" name="admin" readonly="readonly" type="checkbox" value="1" />'
        );
        $this->assertDomEquals(
            check_box_tag('admin', 1, true, array('disabled' => false, 'readonly' => null)),
            '<input checked="checked" id="admin" name="admin" type="checkbox" value="1" />'
        );
        $this->assertDomEquals(
            select_tag('people', '<option>raph</option>', array('multiple' => true)),
            '<select id="people" name="people" multiple="multiple"><option>raph</option></select>'
        );
        $this->assertDomEquals(
            select_tag('people', '<option>raph</option>', array('multiple' => null)),
            '<select id="people" name="people"><option>raph</option></select>'
        );
    }
    
    public function test_submit_tag()
    {
        $this->assertDomEquals(
            submit_tag('Save'),
            '<input name="commit" type="submit" value="Save" />'
        );
        $this->assertDomEquals(
            submit_tag('Save', array('disable_with' => 'Saving...', 'onclick' => 'alert(\'hello !\')')),
            '<input name="commit" type="submit" value="Save" 
            onclick="this.disabled=true;this.value=\'Saving...\';this.form.submit();alert(\'hello !\')" />'
        );
    }
}

