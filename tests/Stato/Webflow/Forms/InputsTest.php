<?php

namespace Stato\Webflow\Forms;

use Stato\Webflow\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

require_once 'Stato/Webflow/Forms/Inputs.php';

class InputsTest extends TestCase
{
    public function testTextInput()
    {
        $i = new TextInput();
        $this->assertEquals('<input type="text" name="title" value="Hello !" />', $i->render('title', 'Hello !'));
    }
    
    public function testTextInputWithoutValue()
    {
        $i = new TextInput();
        $this->assertEquals('<input type="text" name="title" />', $i->render('title'));
    }
    
    public function testTextInputWithQuotedValue()
    {
        $i = new TextInput();
        $this->assertEquals('<input type="text" name="title" value="Jack &quot;Bull&quot; O&#039;Connor" />', $i->render('title', 'Jack "Bull" O\'Connor'));
    }
    
    public function testRenderTextInputWithAttrs()
    {
        $i = new TextInput();
        $this->assertEquals('<input type="text" name="title" id="my_title" class="valid" value="Hello !" />', 
                            $i->render('title', 'Hello !', array('id' => 'my_title', 'class' => 'valid')));
    }
    
    public function testPasswordInput()
    {
        $i = new PasswordInput();
        $this->assertEquals('<input type="password" name="pwd" value="1234" />', $i->render('pwd', '1234'));
    }
    
    public function testHiddenInput()
    {
        $i = new HiddenInput();
        $this->assertEquals('<input type="hidden" name="pwd" value="1234" />', $i->render('pwd', '1234'));
    }
    
    public function testFileInput()
    {
        $i = new FileInput();
        $this->assertEquals('<input type="file" name="image" />', $i->render('image', '1234'));
    }
    
    public function testTextarea()
    {
        $i = new Textarea();
        $this->assertEquals('<textarea name="body" cols="40" rows="10">hello world</textarea>',
                            $i->render('body', 'hello world'));
    }
    
    public function testTextareaWithSizeOptions()
    {
        $i = new Textarea(array('cols' => 80, 'rows' => 20));
        $this->assertEquals('<textarea name="body" cols="80" rows="20">hello world</textarea>',
                            $i->render('body', 'hello world'));
    }
    
    public function testTextDateInput()
    {
        $i = new DateInput();
        $this->assertEquals('<input type="text" name="birthday" value="2009-03-27" />', 
                            $i->render('birthday', new \DateTime('2009-03-27')));
    }
    
    public function testTextDateInputWithFormat()
    {
        $i = new DateInput(array('format' => 'd/m/Y'));
        $this->assertEquals('<input type="text" name="birthday" value="27/03/2009" />', 
                            $i->render('birthday', new \DateTime('2009-03-27')));
    }
    
    public function testTextDateTimeInput()
    {
        $i = new DateTimeInput();
        $this->assertEquals('<input type="text" name="event" value="2009-03-27 16:00:00" />', 
                            $i->render('event', new \DateTime('2009-03-27 16:00:00')));
    }
    
    public function testTextTimeInput()
    {
        $i = new TimeInput();
        $this->assertEquals('<input type="text" name="schedule" value="16:00:00" />', 
                            $i->render('schedule', new \DateTime('2009-03-27 16:00:00')));
    }
    
    public function testCheckboxInput()
    {
        $i = new CheckboxInput();
        $this->assertEquals('<input type="checkbox" name="admin" checked="checked" value="1" />', 
                            $i->render('admin', 1, array('checked' => true)));
    }
    
    public function testCheckboxInputUnchecked()
    {
        $i = new CheckboxInput();
        $this->assertEquals('<input type="checkbox" name="admin" value="1" />', 
                            $i->render('admin', 1));
    }
    
    public function testCheckboxInputDisabled()
    {
        $i = new CheckboxInput(array('disabled' => true));
        $this->assertEquals('<input type="checkbox" name="admin" disabled="disabled" value="1" />', 
                            $i->render('admin', 1));
    }
    
    public function testSelect()
    {
        $s = new Select(array('choices' => array('Marketing', 'IT', 'Commercial')));
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
        $s = new Select(array('choices' => array('Marketing', 'IT', 'Commercial')));
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
        $s = new Select(array('choices' => array(1=>'Marketing', 2=>'IT', 3=>'Commercial')));
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
        $s = new Select(array('choices' => array(
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
        $s = new MultipleSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
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
        $s = new MultipleSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
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
        $s = new MultipleSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<select multiple="multiple" name="service[]">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial" selected="selected">Commercial</option>
            </select>',
            $s->render('service[]', array('Marketing', 'Commercial'))
        );
    }
    
    /*public function testCheckboxMultipleSelect()
    {
        $s = new CheckboxMultipleSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertDomEquals(
            '<ul>
            <li><label><input type="checkbox" name="service" multiple="multiple" value="Marketing" />Marketing</label></li>
            <li><label><input type="checkbox" name="service" multiple="multiple" value="IT" checked="checked" />IT</label></li>
            <li><label><input type="checkbox" name="service" multiple="multiple" value="Commercial" />Commercial</label></li>
            </ul>',
            $s->render('service', 'IT')
        );
    }*/
    
    public function testRadioSelect()
    {
        $s = new RadioSelect(array('choices' => array('Marketing', 'IT', 'Commercial')));
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
        $s = new RadioSelect(array('id' => 'foo_service', 'choices' => array('Marketing', 'IT', 'Commercial')));
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
        $s = new RadioSelect(array('choices' => array(
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
        $s = new RadioSelect(array('id' => 'foo_skill', 'choices' => array(
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
}