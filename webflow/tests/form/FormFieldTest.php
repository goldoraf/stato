<?php

require_once dirname(__FILE__) . '/../../../tests/TestsHelper.php';

require_once 'form/field.php';
require_once 'form/input.php';
require_once 'request.php';

class Stato_FormFieldTest extends PHPUnit_Framework_TestCase
{
    public function testFieldClean()
    {
        $f = new Stato_Form_Field();
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function testRequiredField()
    {
        $this->setExpectedException('Stato_Form_ValidationError');
        $f = new Stato_Form_Field(array('required' => true));
        $f->clean('');
    }
    
    public function testFieldWithCustomErrorMessage()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Plz give me something!');
        $f = new Stato_Form_Field(array('required' => true, 'error_messages' => array('required' => 'Plz give me something!')));
        $f->clean('');
    }
    
    public function testFieldRender()
    {
        $f = new Stato_Form_Field();
        $this->assertEquals('<input type="text" name="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function testFieldRenderWithHtmlAttrs()
    {
        $f = new Stato_Form_Field();
        $this->assertEquals('<input type="text" name="foo" id="id_foo" value="bar" />', $f->render('foo', 'bar', array('id' => 'id_foo')));
    }
    
    public function testFieldBind()
    {
        $f = new Stato_Form_Field();
        $this->assertEquals('<input type="text" name="foo" value="bar" />', $f->bind('foo', 'bar')->__toString());
    }
    
    public function testCharFieldWithLengthOption()
    {
        $f = new Stato_Form_CharField(array('length' => 3));
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function testCharFieldWithMaxLengthOption()
    {
        $f = new Stato_Form_CharField(array('max_length' => 6));
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function testCharFieldWithMinLengthOption()
    {
        $f = new Stato_Form_CharField(array('min_length' => 2));
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function testCharFieldWithMinLengthOptionRender()
    {
        $f = new Stato_Form_CharField(array('min_length' => 2));
        $this->assertEquals('<input type="text" name="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function testCharFieldWithMaxLengthOptionRender()
    {
        $f = new Stato_Form_CharField(array('max_length' => 6));
        $this->assertEquals('<input type="text" name="foo" maxlength="6" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function testCharFieldLengthValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Ensure this value has %d characters (it has %d).');
        $f = new Stato_Form_CharField(array('length' => 8));
        $f->clean('bar');
    }
    
    public function testCharFieldMaxLengthValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Ensure this value has at most %d characters (it has %d).');
        $f = new Stato_Form_CharField(array('max_length' => 8));
        $f->clean('barbarbar');
    }
    
    public function testCharFieldMinLengthValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Ensure this value has at least %d characters (it has %d).');
        $f = new Stato_Form_CharField(array('min_length' => 4));
        $f->clean('bar');
    }
    
    public function testCharFieldWithRegexOption()
    {
        $f = new Stato_Form_CharField(array('regex' => '/^a/'));
        $this->assertEquals('abc', $f->clean('abc'));
    }
    
    public function testCharFieldWithRegexOptionValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError');
        $f = new Stato_Form_CharField(array('regex' => '/^a/'));
        $f->clean('bc');
    }
    
    public function testCharFieldValidationErrorShouldSanitize()
    {
        try {
            $f = new Stato_Form_CharField(array('regex' => '/^a/'));
            $f->clean('<xss></xss>bc');
        } catch (Stato_Form_ValidationError $e) {
            $this->assertEquals('bc', $e->getCleanedValue());
        }
    }
    
    public function testIntegerField()
    {
        $f = new Stato_Form_IntegerField();
        $this->assertEquals(3, $f->clean('3'));
    }
    
    public function testIntegerFieldWithMaxValueOption()
    {
        $f = new Stato_Form_IntegerField(array('max_value' => 6));
        $this->assertEquals(5, $f->clean('5'));
    }
    
    public function testIntegerFieldWithMinValueOption()
    {
        $f = new Stato_Form_IntegerField(array('min_value' => 2));
        $this->assertEquals(5, $f->clean('5'));
    }
    
    public function testIntegerFieldMaxValueValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Ensure this value is greater than or equal to %s.');
        $f = new Stato_Form_IntegerField(array('max_value' => 8));
        $f->clean('12');
    }
    
    public function testIntegerFieldMinValueValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Ensure this value is less than or equal to %s.');
        $f = new Stato_Form_IntegerField(array('min_value' => 4));
        $f->clean('3');
    }
    
    public function testDateTimeField()
    {
        $f = new Stato_Form_DateTimeField();
        $this->assertEquals(new DateTime('2009-03-28'), $f->clean('2009-03-28'));
    }
    
    public function testDateTimeFieldValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Enter a valid date.');
        $f = new Stato_Form_DateTimeField();
        $f->clean('2009-03-32');
    }
    
    public function testEmailField()
    {
        $f = new Stato_Form_EmailField();
        $this->assertEquals('john@doe.net', $f->clean('john@doe.net'));
    }
    
    public function testEmailFieldValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Enter a valid e-mail address.');
        $f = new Stato_Form_EmailField();
        $f->clean('john');
    }
    
    public function testUrlField()
    {
        $f = new Stato_Form_UrlField();
        $this->assertEquals('http://stato-framework.org', $f->clean('http://stato-framework.org'));
    }
    
    public function testUrlFieldValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Enter a valid URL.');
        $f = new Stato_Form_UrlField();
        $f->clean('http:/stato-framework.org');
    }
    
    public function testIpField()
    {
        $f = new Stato_Form_IpField();
        $this->assertEquals('127.0.0.1', $f->clean('127.0.0.1'));
    }
    
    public function testIpFieldValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Enter a valid IP.');
        $f = new Stato_Form_IpField();
        $f->clean('127.0.0.1.1');
    }
    
    public function testBooleanFieldWithTrueValues()
    {
        $f = new Stato_Form_BooleanField();
        $this->assertTrue($f->clean('true'));
        $this->assertTrue($f->clean('on'));
        $this->assertTrue($f->clean('yes'));
        $this->assertTrue($f->clean('1'));
    }
    
    public function testBooleanFieldWithFalseValues()
    {
        $f = new Stato_Form_BooleanField();
        $this->assertFalse($f->clean('false'));
        $this->assertFalse($f->clean('off'));
        $this->assertFalse($f->clean('no'));
        $this->assertFalse($f->clean('0'));
    }
    
    public function testBooleanFieldWithNotBooleanValue()
    {
        $f = new Stato_Form_BooleanField();
        $this->assertFalse($f->clean('foo'));
    }
    
    public function testRequiredBooleanField()
    {
        $f = new Stato_Form_BooleanField(array('required' => true));
        $this->assertTrue($f->clean('1'));
    }
    
    public function testRequiredBooleanFieldWithFalseValue()
    {
        $this->setExpectedException('Stato_Form_ValidationError');
        $f = new Stato_Form_BooleanField(array('required' => true));
        $f->clean('0');
    }
    
    public function testBooleanFieldRender()
    {
        $f = new Stato_Form_BooleanField();
        $this->assertEquals('<input type="hidden" name="foo" value="0" /><input type="checkbox" name="foo" value="1" />',
                            $f->render('foo'));
    }
    
    public function testBooleanFieldRenderWithValueOptions()
    {
        $f = new Stato_Form_BooleanField(array('checked_value' => 'true', 'unchecked_value' => 'false'));
        $this->assertEquals('<input type="hidden" name="foo" value="false" /><input type="checkbox" name="foo" value="true" />',
                            $f->render('foo'));
    }
    
    public function testBooleanFieldRenderChecked()
    {
        $f = new Stato_Form_BooleanField();
        $this->assertEquals('<input type="hidden" name="foo" value="0" /><input type="checkbox" name="foo" checked="checked" value="1" />',
                            $f->render('foo', true));
    }
    
    public function testChoiceField()
    {
        $f = new Stato_Form_ChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertEquals('IT', $f->clean('IT'));
    }
    
    public function testChoiceFieldWithAssociativeArray()
    {
        $f = new Stato_Form_ChoiceField(array('choices' => array(1=>'Marketing', 2=>'IT', 3=>'Commercial')));
        $this->assertEquals('2', $f->clean('2'));
    }
    
    public function testChoiceFieldWithMultidimensionalArray()
    {
        $f = new Stato_Form_ChoiceField(array('choices' => array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        )));
        $this->assertEquals('PHP', $f->clean('PHP'));
    }
    
    public function testChoiceFieldValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Select a valid choice.');
        $f = new Stato_Form_ChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $f->clean('HR');
    }
    
    public function testChoiceFieldWithAssociativeArrayValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Select a valid choice.');
        $f = new Stato_Form_ChoiceField(array('choices' => array(1=>'Marketing', 2=>'IT', 3=>'Commercial')));
        $f->clean('4');
    }
    
    public function testChoiceFieldWithMultidimensionalArrayValidationError()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'Select a valid choice.');
        $f = new Stato_Form_ChoiceField(array('choices' => array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        )));
        $f->clean('Java');
    }
    
    public function testFileFieldWithoutUploadedFile()
    {
        $f = new Stato_Form_FileField();
        $this->assertEquals(null, $f->clean(null));
    }
    
    public function testFileFieldWithNotUploadedFile()
    {
        $this->setExpectedException('Stato_Form_ValidationError', 'No file was submitted.');
        $f = new Stato_Form_FileField();
        $f->clean(new Stato_UploadedFile('test.jpg', '/tmp/1234', 'image/jpeg', 123, 0));
    }
}