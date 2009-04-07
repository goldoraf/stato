<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

class SFormInputTest extends PHPUnit_Framework_TestCase
{
    public function test_text_input()
    {
        $i = new STextInput();
        $this->assertEquals('<input type="text" name="title" value="Hello !" />', $i->render('title', 'Hello !'));
    }
    
    public function test_text_input_without_value()
    {
        $i = new STextInput();
        $this->assertEquals('<input type="text" name="title" />', $i->render('title'));
    }
    
    public function test_render_text_input_with_attrs()
    {
        $i = new STextInput();
        $this->assertEquals('<input type="text" name="title" id="my_title" class="valid" value="Hello !" />', 
                            $i->render('title', 'Hello !', array('id' => 'my_title', 'class' => 'valid')));
    }
    
    public function test_password_input()
    {
        $i = new SPasswordInput();
        $this->assertEquals('<input type="password" name="pwd" value="1234" />', $i->render('pwd', '1234'));
    }
    
    public function test_hidden_input()
    {
        $i = new SHiddenInput();
        $this->assertEquals('<input type="hidden" name="pwd" value="1234" />', $i->render('pwd', '1234'));
    }
    
    public function test_file_input()
    {
        $i = new SFileInput();
        $this->assertEquals('<input type="file" name="image" />', $i->render('image', '1234'));
    }
    
    public function test_textarea()
    {
        $i = new STextarea();
        $this->assertEquals('<textarea name="body" cols="40" rows="10">hello world</textarea>',
                            $i->render('body', 'hello world'));
    }
    
    public function test_textarea_with_size_options()
    {
        $i = new STextarea(array('cols' => 80, 'rows' => 20));
        $this->assertEquals('<textarea name="body" cols="80" rows="20">hello world</textarea>',
                            $i->render('body', 'hello world'));
    }
    
    public function test_text_date_input()
    {
        $i = new SDateInput();
        $this->assertEquals('<input type="text" name="birthday" value="2009-03-27" />', 
                            $i->render('birthday', new DateTime('2009-03-27')));
    }
    
    public function test_text_date_input_with_format()
    {
        $i = new SDateInput(array('format' => 'd/m/Y'));
        $this->assertEquals('<input type="text" name="birthday" value="27/03/2009" />', 
                            $i->render('birthday', new DateTime('2009-03-27')));
    }
    
    public function test_text_date_time_input()
    {
        $i = new SDateTimeInput();
        $this->assertEquals('<input type="text" name="event" value="2009-03-27 16:00:00" />', 
                            $i->render('event', new DateTime('2009-03-27 16:00:00')));
    }
    
    public function test_text_time_input()
    {
        $i = new STimeInput();
        $this->assertEquals('<input type="text" name="schedule" value="16:00:00" />', 
                            $i->render('schedule', new DateTime('2009-03-27 16:00:00')));
    }
    
    public function test_checkbox_input()
    {
        $i = new SCheckboxInput();
        $this->assertEquals('<input type="checkbox" name="admin" checked="checked" value="1" />', 
                            $i->render('admin', 1, array('checked' => true)));
    }
    
    public function test_checkbox_input_unchecked()
    {
        $i = new SCheckboxInput();
        $this->assertEquals('<input type="checkbox" name="admin" value="1" />', 
                            $i->render('admin', 1));
    }
    
    public function test_checkbox_input_disabled()
    {
        $i = new SCheckboxInput(array('disabled' => true));
        $this->assertEquals('<input type="checkbox" name="admin" disabled="disabled" value="1" />', 
                            $i->render('admin', 1));
    }
    
    public function test_select()
    {
        $s = new SSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select name="service">
            <option value="Marketing">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service')
        );
    }
    
    public function test_select_with_selected_option()
    {
        $s = new SSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select name="service">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service', 'Marketing')
        );
    }
    
    public function test_select_with_associative_array()
    {
        $s = new SSelect(array('choices' => array(1=>'Marketing', 2=>'IT', 3=>'Commercial')));
        $this->assertDomEquals(
            '<select name="service">
            <option value="1" selected="selected">Marketing</option>
            <option value="2">IT</option>
            <option value="3">Commercial</option>
            </select>',
            $s->render('service', 1)
        );
    }
    
    public function test_select_with_multidimensional_array()
    {
        $s = new SSelect(array('choices' => array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        )));
        $this->assertDomEquals(
            '<select name="skills">
            <optgroup label="languages">
            <option value="PHP" selected="selected">PHP</option>
            <option value="Python">Python</option>
            <option value="Ruby">Ruby</option>
            </optgroup>
            <optgroup label="os">
            <option value="Linux">Linux</option>
            <option value="MacOS">MacOS</option>
            <option value="Windows">Windows</option>
            </optgroup>
            </select>',
            $s->render('skills', 'PHP')
        );
    }
    
    public function test_multiple_select()
    {
        $s = new SMultipleSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select multiple="multiple" name="service">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service', 'Marketing')
        );
    }
    
    public function test_multiple_select_with_multiple_choices()
    {
        $s = new SMultipleSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select multiple="multiple" name="service">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial" selected="selected">Commercial</option>
            </select>',
            $s->render('service', array('Marketing', 'Commercial'))
        );
    }
    
    private function assertDomEquals($str1, $str2)
    {
    	$this->assertXmlStringEqualsXmlString("<root>$str1</root>", "<root>$str2</root>");
    }
}