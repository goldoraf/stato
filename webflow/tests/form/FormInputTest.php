<?php

require_once dirname(__FILE__) . '/../../../tests/TestsHelper.php';

require_once 'helpers/string.php';
require_once 'form/input.php';

class Stato_FormInputTest extends PHPUnit_Framework_TestCase
{
    public function testTextInput()
    {
        $i = new Stato_Form_TextInput();
        $this->assertEquals('<input type="text" name="title" value="Hello !" />', $i->render('title', 'Hello !'));
    }
    
    public function testTextInputWithoutValue()
    {
        $i = new Stato_Form_TextInput();
        $this->assertEquals('<input type="text" name="title" />', $i->render('title'));
    }
    
    public function testRenderTextInputWithAttrs()
    {
        $i = new Stato_Form_TextInput();
        $this->assertEquals('<input type="text" name="title" id="my_title" class="valid" value="Hello !" />', 
                            $i->render('title', 'Hello !', array('id' => 'my_title', 'class' => 'valid')));
    }
    
    public function testPasswordInput()
    {
        $i = new Stato_Form_PasswordInput();
        $this->assertEquals('<input type="password" name="pwd" value="1234" />', $i->render('pwd', '1234'));
    }
    
    public function testHiddenInput()
    {
        $i = new Stato_Form_HiddenInput();
        $this->assertEquals('<input type="hidden" name="pwd" value="1234" />', $i->render('pwd', '1234'));
    }
    
    public function testFileInput()
    {
        $i = new Stato_Form_FileInput();
        $this->assertEquals('<input type="file" name="image" />', $i->render('image', '1234'));
    }
    
    public function testTextarea()
    {
        $i = new Stato_Form_Textarea();
        $this->assertEquals('<textarea name="body" cols="40" rows="10">hello world</textarea>',
                            $i->render('body', 'hello world'));
    }
    
    public function testTextareaWithSizeOptions()
    {
        $i = new Stato_Form_Textarea(array('cols' => 80, 'rows' => 20));
        $this->assertEquals('<textarea name="body" cols="80" rows="20">hello world</textarea>',
                            $i->render('body', 'hello world'));
    }
    
    public function testTextDateInput()
    {
        $i = new Stato_Form_DateInput();
        $this->assertEquals('<input type="text" name="birthday" value="2009-03-27" />', 
                            $i->render('birthday', new DateTime('2009-03-27')));
    }
    
    public function testTextDateInputWithFormat()
    {
        $i = new Stato_Form_DateInput(array('format' => 'd/m/Y'));
        $this->assertEquals('<input type="text" name="birthday" value="27/03/2009" />', 
                            $i->render('birthday', new DateTime('2009-03-27')));
    }
    
    public function testTextDateTimeInput()
    {
        $i = new Stato_Form_DateTimeInput();
        $this->assertEquals('<input type="text" name="event" value="2009-03-27 16:00:00" />', 
                            $i->render('event', new DateTime('2009-03-27 16:00:00')));
    }
    
    public function testTextTimeInput()
    {
        $i = new Stato_Form_TimeInput();
        $this->assertEquals('<input type="text" name="schedule" value="16:00:00" />', 
                            $i->render('schedule', new DateTime('2009-03-27 16:00:00')));
    }
    
    public function testCheckboxInput()
    {
        $i = new Stato_Form_CheckboxInput();
        $this->assertEquals('<input type="checkbox" name="admin" checked="checked" value="1" />', 
                            $i->render('admin', 1, array('checked' => true)));
    }
    
    public function testCheckboxInputUnchecked()
    {
        $i = new Stato_Form_CheckboxInput();
        $this->assertEquals('<input type="checkbox" name="admin" value="1" />', 
                            $i->render('admin', 1));
    }
    
    public function testCheckboxInputDisabled()
    {
        $i = new Stato_Form_CheckboxInput(array('disabled' => true));
        $this->assertEquals('<input type="checkbox" name="admin" disabled="disabled" value="1" />', 
                            $i->render('admin', 1));
    }
    
    public function testSelect()
    {
        $s = new Stato_Form_Select(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select name="service">
            <option value="Marketing">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service')
        );
    }
    
    public function testSelectWithSelectedOption()
    {
        $s = new Stato_Form_Select(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select name="service">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service', 'Marketing')
        );
    }
    
    public function testSelectWithAssociativeArray()
    {
        $s = new Stato_Form_Select(array('choices' => array(1=>'Marketing', 2=>'IT', 3=>'Commercial')));
        $this->assertDomEquals(
            '<select name="service">
            <option value="1" selected="selected">Marketing</option>
            <option value="2">IT</option>
            <option value="3">Commercial</option>
            </select>',
            $s->render('service', 1)
        );
    }
    
    public function testSelectWithMultidimensionalArray()
    {
        $s = new Stato_Form_Select(array('choices' => array(
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
    
    public function testMultipleSelect()
    {
        $s = new Stato_Form_MultipleSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select multiple="multiple" name="service[]">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service[]', 'Marketing')
        );
    }
    
    public function testMultipleSelectShouldAddSquareBracketsToNameAttr()
    {
        $s = new Stato_Form_MultipleSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select multiple="multiple" name="service[]">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            $s->render('service', 'Marketing')
        );
    }
    
    public function testMultipleSelectWithMultipleChoices()
    {
        $s = new Stato_Form_MultipleSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select multiple="multiple" name="service[]">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial" selected="selected">Commercial</option>
            </select>',
            $s->render('service[]', array('Marketing', 'Commercial'))
        );
    }
    
    public function testRadioSelect()
    {
        $s = new Stato_Form_RadioSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<ul>
            <li><label><input type="radio" name="service" value="Marketing" />Marketing</label></li>
            <li><label><input type="radio" name="service" checked="checked" value="IT" />IT</label></li>
            <li><label><input type="radio" name="service" value="Commercial" />Commercial</label></li>
            </ul>',
            $s->render('service', 'IT')
        );
    }
    
    public function testRadioSelectWithIdOption()
    {
        $s = new Stato_Form_RadioSelect(array('id' => 'foo_service', 'choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<ul>
            <li><label for="foo_service_1"><input type="radio" name="service" id="foo_service_1" value="Marketing" />Marketing</label></li>
            <li><label for="foo_service_2"><input type="radio" name="service" id="foo_service_2" value="IT" checked="checked" />IT</label></li>
            <li><label for="foo_service_3"><input type="radio" name="service" id="foo_service_3" value="Commercial" />Commercial</label></li>
            </ul>',
            $s->render('service', 'IT')
        );
    }
    
    public function testRadioSelectWithAssociativeArray()
    {
        $s = new Stato_Form_RadioSelect(array('choices' => array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        )));
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
    
    public function testRadioSelectWithAssociativeArrayAndIdOption()
    {
        $s = new Stato_Form_RadioSelect(array('id' => 'foo_skill', 'choices' => array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        )));
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