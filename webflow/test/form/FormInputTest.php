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
    
    public function test_text_input_with_quoted_value()
    {
        $i = new STextInput();
        $this->assertEquals('<input type="text" name="title" value="Jack &quot;Bull&quot; O&#039;Connor" />', $i->render('title', 'Jack "Bull" O\'Connor'));
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
                            $i->render('birthday', new SDate(2009, 03, 27)));
    }
    
    public function test_text_date_input_with_format()
    {
        $i = new SDateInput(array(), array('format' => '%d/%m/%Y'));
        $this->assertEquals('<input type="text" name="birthday" value="27/03/2009" />', 
                            $i->render('birthday', new SDate(2009, 03, 27)));
    }
    
    public function test_text_date_time_input()
    {
        $i = new SDateTimeInput();
        $this->assertEquals('<input type="text" name="event" value="2009-03-27 16:00:00" />', 
                            $i->render('event', new SDateTime(2009, 03, 27, 16, 0, 0)));
    }
    
    public function test_text_time_input()
    {
        $i = new STimeInput();
        $this->assertEquals('<input type="text" name="schedule" value="16:00:00" />', 
                            $i->render('schedule', new SDateTime(2009, 03, 27, 16, 0, 0)));
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
        $s = new SSelect();
        $s->set_choices(array('Marketing', 'IT', 'Commercial'));
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
        $s = new SSelect();
        $s->set_choices(array('Marketing', 'IT', 'Commercial'));
        $this->assertDomEquals(
            '<select name="service">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service', 'Marketing')
        );
    }
    
    public function test_select_with_include_blank_option()
    {
        $s = new SSelect(array(), array('include_blank' => true));
        $s->set_choices(array('Marketing', 'IT', 'Commercial'));
        $this->assertDomEquals(
            '<select name="service">
            <option value=""></option>
            <option value="Marketing">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service')
        );
    }
    
    public function test_select_with_include_prompt_option()
    {
        $s = new SSelect(array(), array('include_prompt' => 'Plz make a choice'));
        $s->set_choices(array('Marketing', 'IT', 'Commercial'));
        $this->assertDomEquals(
            '<select name="service">
            <option value="">Plz make a choice</option>
            <option value="Marketing">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service')
        );
    }
    
    public function test_select_with_associative_array()
    {
        $s = new SSelect();
        $s->set_choices(array(1=>'Marketing', 2=>'IT', 3=>'Commercial'));
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
        $s = new SSelect();
        $s->set_choices(array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        ));
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
        $s = new SMultipleSelect();
        $s->set_choices(array('Marketing', 'IT', 'Commercial'));
        $this->assertDomEquals(
            '<select multiple="multiple" name="service[]">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service[]', 'Marketing')
        );
    }
    
    public function test_multiple_select_should_add_square_brackets_to_name_attr()
    {
        $s = new SMultipleSelect();
        $s->set_choices(array('Marketing', 'IT', 'Commercial'));
        $this->assertDomEquals(
            '<select multiple="multiple" name="service[]">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service', 'Marketing')
        );
    }
    
    public function test_multiple_select_with_multiple_choices()
    {
        $s = new SMultipleSelect();
        $s->set_choices(array('Marketing', 'IT', 'Commercial'));
        $this->assertDomEquals(
            '<select multiple="multiple" name="service[]">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial" selected="selected">Commercial</option>
            </select>',
            $s->render('service[]', array('Marketing', 'Commercial'))
        );
    }
    
    public function test_radio_select()
    {
        $s = new SRadioSelect();
        $s->set_choices(array('Marketing', 'IT', 'Commercial'));
        $this->assertDomEquals(
            '<ul>
            <li><label><input type="radio" name="service" value="Marketing" />Marketing</label></li>
            <li><label><input type="radio" name="service" checked="checked" value="IT" />IT</label></li>
            <li><label><input type="radio" name="service" value="Commercial" />Commercial</label></li>
            </ul>',
            $s->render('service', 'IT')
        );
    }
    
    public function test_radio_select_with_id_option()
    {
        $s = new SRadioSelect(array('id' => 'foo_service'));
        $s->set_choices(array('Marketing', 'IT', 'Commercial'));
        $this->assertDomEquals(
            '<ul>
            <li><label for="foo_service_1"><input type="radio" name="service" id="foo_service_1" value="Marketing" />Marketing</label></li>
            <li><label for="foo_service_2"><input type="radio" name="service" id="foo_service_2" value="IT" checked="checked" />IT</label></li>
            <li><label for="foo_service_3"><input type="radio" name="service" id="foo_service_3" value="Commercial" />Commercial</label></li>
            </ul>',
            $s->render('service', 'IT')
        );
    }
    
    public function test_radio_select_with_associative_array()
    {
        $s = new SRadioSelect();
        $s->set_choices(array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        ));
        $this->assertDomEquals(
            '<ul>
            <li>languages</li>
            <ul>
            <li><label><input type="radio" name="skill" value="PHP" />PHP</label></li>
            <li><label><input type="radio" name="skill" value="Python" />Python</label></li>
            <li><label><input type="radio" name="skill" value="Ruby" />Ruby</label></li>
            </ul>
            <li>os</li>
            <ul>
            <li><label><input type="radio" name="skill" checked="checked" value="Linux" />Linux</label></li>
            <li><label><input type="radio" name="skill" value="MacOS" />MacOS</label></li>
            <li><label><input type="radio" name="skill" value="Windows" />Windows</label></li>
            </ul>
            </ul>',
            $s->render('skill', 'Linux')
        );
    }
    
    public function test_radio_select_with_associative_array_and_id_option()
    {
        $s = new SRadioSelect(array('id' => 'foo_skill'));
        $s->set_choices(array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        ));
        $this->assertDomEquals(
            '<ul>
            <li>languages</li>
            <ul>
            <li><label for="foo_skill_1"><input type="radio" name="skill" id="foo_skill_1" value="PHP" />PHP</label></li>
            <li><label for="foo_skill_2"><input type="radio" name="skill" id="foo_skill_2" value="Python" />Python</label></li>
            <li><label for="foo_skill_3"><input type="radio" name="skill" id="foo_skill_3" value="Ruby" />Ruby</label></li>
            </ul>
            <li>os</li>
            <ul>
            <li><label for="foo_skill_4"><input type="radio" name="skill" id="foo_skill_4" value="Linux" checked="checked" />Linux</label></li>
            <li><label for="foo_skill_5"><input type="radio" name="skill" id="foo_skill_5" value="MacOS" />MacOS</label></li>
            <li><label for="foo_skill_6"><input type="radio" name="skill" id="foo_skill_6" value="Windows" />Windows</label></li>
            </ul>
            </ul>',
            $s->render('skill', 'Linux')
        );
    }
    
    private function assertDomEquals($str1, $str2)
    {
        $this->assertXmlStringEqualsXmlString("<root>$str1</root>", "<root>$str2</root>");
    }
}