<?php

namespace Stato\Webflow\Forms;

use Stato\Webflow\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

require_once 'Stato/Webflow/Forms/Inputs.php';
require_once 'Stato/Webflow/Forms/Fields.php';

class FieldsTest extends TestCase
{
    public function testFieldClean()
    {
        $f = new Field();
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function testRequiredField()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError');
        $f = new Field(array('required' => true));
        $f->clean('');
    }
    
    public function testFieldWithCustomErrorMessage()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Plz give me something!');
        $f = new Field(array('required' => true, 'error_messages' => array('required' => 'Plz give me something!')));
        $f->clean('');
    }
    
    public function testFieldRender()
    {
        $f = new Field();
        $this->assertEquals('<input type="text" name="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function testFieldRenderWithHtmlAttrs()
    {
        $f = new Field();
        $this->assertEquals('<input type="text" name="foo" id="id_foo" value="bar" />', $f->render('foo', 'bar', array('id' => 'id_foo')));
    }
    
    public function testFieldWithInputOption()
    {
        $f = new Field(array('input' => new HiddenInput));
        $this->assertEquals('<input type="hidden" name="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function testFieldWithInputAttrsOption()
    {
        $f = new Field(array('input_attrs' => array('class' => 'foo')));
        $this->assertEquals('<input type="text" name="foo" class="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function testFieldWithBadInputOptionShouldThrow()
    {
        $this->setExpectedException('Exception');
        $f = new Field(array('input' => new ValidationError));
    }
    
    public function testFieldBind()
    {
        $f = new Field();
        $this->assertEquals('<input type="text" name="foo" value="bar" />', $f->bind('foo', 'bar')->__toString());
    }
    
    public function testCharFieldNoEncodeQuotes()
    {
        $f = new CharField();
        $this->assertEquals('foo\'bar', $f->clean('foo\'bar'));
    }
    
    public function testCharFieldNoEncodeAmp()
    {
        $f = new CharField();
        $this->assertEquals('foo&bar', $f->clean('foo&bar'));
    }
    
    public function testCharFieldWithLengthOption()
    {
        $f = new CharField(array('length' => 3));
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function testCharFieldWithMaxLengthOption()
    {
        $f = new CharField(array('max_length' => 6));
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function testCharFieldWithMinLengthOption()
    {
        $f = new CharField(array('min_length' => 2));
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function testCharFieldWithMinLengthOptionRender()
    {
        $f = new CharField(array('min_length' => 2));
        $this->assertEquals('<input type="text" name="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function testCharFieldWithMaxLengthOptionRender()
    {
        $f = new CharField(array('max_length' => 6));
        $this->assertEquals('<input type="text" name="foo" maxlength="6" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function testCharFieldLengthValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Ensure this value has %d characters (it has %d).');
        $f = new CharField(array('length' => 8));
        $f->clean('bar');
    }
    
    public function testCharFieldMaxLengthValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Ensure this value has at most %d characters (it has %d).');
        $f = new CharField(array('max_length' => 8));
        $f->clean('barbarbar');
    }
    
    public function testCharFieldMinLengthValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Ensure this value has at least %d characters (it has %d).');
        $f = new CharField(array('min_length' => 4));
        $f->clean('bar');
    }
    
    public function testCharFieldWithRegexOption()
    {
        $f = new CharField(array('regex' => '/^a/'));
        $this->assertEquals('abc', $f->clean('abc'));
    }
    
    public function testCharFieldWithRegexOptionValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError');
        $f = new CharField(array('regex' => '/^a/'));
        $f->clean('bc');
    }
    
    public function testCharFieldValidationErrorShouldSanitize()
    {
        try {
            $f = new CharField(array('regex' => '/^a/'));
            $f->clean('<xss></xss>bc');
        } catch (ValidationError $e) {
            $this->assertEquals('bc', $e->getCleanedValue());
        }
    }
    
    public function testTextFieldWithInputAttrsOption()
    {
        $f = new TextField(array('input_attrs' => array('cols' => 80, 'rows' => 20)));
        $this->assertEquals('<textarea name="bar" cols="80" rows="20"></textarea>', $f->render('bar'));
    }
    
    public function testIntegerField()
    {
        $f = new IntegerField();
        $this->assertEquals(3, $f->clean('3'));
    }
    
    public function testIntegerFieldWithMaxValueOption()
    {
        $f = new IntegerField(array('max_value' => 6));
        $this->assertEquals(5, $f->clean('5'));
    }
    
    public function testIntegerFieldWithMinValueOption()
    {
        $f = new IntegerField(array('min_value' => 2));
        $this->assertEquals(5, $f->clean('5'));
    }
    
    public function testIntegerFieldMaxValueValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Ensure this value is greater than or equal to %s.');
        $f = new IntegerField(array('max_value' => 8));
        $f->clean('12');
    }
    
    public function testIntegerFieldMinValueValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Ensure this value is less than or equal to %s.');
        $f = new IntegerField(array('min_value' => 4));
        $f->clean('3');
    }
    
    public function testFloatField()
    {
        $f = new FloatField();
        $this->assertEquals(1.234, $f->clean('1.234'));
        $this->assertEquals(1.2e3, $f->clean('1.2e3'));
        $this->assertEquals(7E-10, $f->clean('7E-10'));
    }
    
    public function testFloatFieldWithMaxValueOption()
    {
        $f = new FloatField(array('max_value' => 6));
        $this->assertEquals(5.99, $f->clean('5.99'));
    }
    
    public function testFloatFieldWithMinValueOption()
    {
        $f = new FloatField(array('min_value' => 2));
        $this->assertEquals(2.01, $f->clean('2.01'));
    }
    
    public function testFloatFieldMaxValueValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Ensure this value is greater than or equal to %s.');
        $f = new FloatField(array('max_value' => 8));
        $f->clean('8.01');
    }
    
    public function testFloatFieldMinValueValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Ensure this value is less than or equal to %s.');
        $f = new FloatField(array('min_value' => 4));
        $f->clean('3.99');
    }
    
    public function testDateTimeField()
    {
        $f = new DateTimeField();
        $this->assertEquals(new \DateTime('2009-03-28'), $f->clean('2009-03-28'));
    }
    
    public function testDateTimeFieldValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Enter a valid date.');
        $f = new DateTimeField();
        $f->clean('2009-03-32');
    }
    
    public function testEmailField()
    {
        $f = new EmailField();
        $this->assertEquals('john@doe.net', $f->clean('john@doe.net'));
    }
    
    public function testEmailFieldValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Enter a valid e-mail address.');
        $f = new EmailField();
        $f->clean('john');
    }
    
    public function testUrlField()
    {
        $f = new UrlField();
        $this->assertEquals('http://stato-framework.org', $f->clean('http://stato-framework.org'));
    }
    
    public function testUrlFieldValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Enter a valid URL.');
        $f = new UrlField();
        $f->clean('http:/stato-framework.org');
    }
    
    public function testIpField()
    {
        $f = new IpField();
        $this->assertEquals('127.0.0.1', $f->clean('127.0.0.1'));
    }
    
    public function testIpFieldValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Enter a valid IP.');
        $f = new IpField();
        $f->clean('127.0.0.1.1');
    }
    
    public function testBooleanFieldWithTrueValues()
    {
        $f = new BooleanField();
        $this->assertTrue($f->clean('true'));
        $this->assertTrue($f->clean('on'));
        $this->assertTrue($f->clean('yes'));
        $this->assertTrue($f->clean('1'));
    }
    
    public function testBooleanFieldWithFalseValues()
    {
        $f = new BooleanField();
        $this->assertFalse($f->clean('false'));
        $this->assertFalse($f->clean('off'));
        $this->assertFalse($f->clean('no'));
        $this->assertFalse($f->clean('0'));
    }
    
    public function testBooleanFieldWithNotBooleanValue()
    {
        $f = new BooleanField();
        $this->assertFalse($f->clean('foo'));
    }
    
    public function testRequiredBooleanField()
    {
        $f = new BooleanField(array('required' => true));
        $this->assertTrue($f->clean('1'));
    }
    
    public function testRequiredBooleanFieldWithFalseValue()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError');
        $f = new BooleanField(array('required' => true));
        $f->clean('0');
    }
    
    public function testBooleanFieldRender()
    {
        $f = new BooleanField();
        $this->assertEquals('<input type="hidden" name="foo" value="0" /><input type="checkbox" name="foo" value="1" />',
                            $f->render('foo'));
    }
    
    public function testBooleanFieldRenderWithValueOptions()
    {
        $f = new BooleanField(array('checked_value' => 'true', 'unchecked_value' => 'false'));
        $this->assertEquals('<input type="hidden" name="foo" value="false" /><input type="checkbox" name="foo" value="true" />',
                            $f->render('foo'));
    }
    
    public function testBooleanFieldRenderChecked()
    {
        $f = new BooleanField();
        $this->assertEquals('<input type="hidden" name="foo" value="0" /><input type="checkbox" name="foo" checked="checked" value="1" />',
                            $f->render('foo', true));
    }
    
    public function testChoiceField()
    {
        $f = new ChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertEquals('IT', $f->clean('IT'));
    }
    
    public function testChoiceFieldWithAssociativeArray()
    {
        $f = new ChoiceField(array('choices' => array(1=>'Marketing', 2=>'IT', 3=>'Commercial')));
        $this->assertEquals('2', $f->clean('2'));
    }
    
    public function testChoiceFieldWithMultidimensionalArray()
    {
        $f = new ChoiceField(array('choices' => array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        )));
        $this->assertEquals('PHP', $f->clean('PHP'));
    }
    
    public function testChoiceFieldValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Select a valid choice.');
        $f = new ChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $f->clean('HR');
    }
    
    public function testChoiceFieldWithAssociativeArrayValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Select a valid choice.');
        $f = new ChoiceField(array('choices' => array(1=>'Marketing', 2=>'IT', 3=>'Commercial')));
        $f->clean('4');
    }
    
    public function testChoiceFieldWithMultidimensionalArrayValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Select a valid choice.');
        $f = new ChoiceField(array('choices' => array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        )));
        $f->clean('Java');
    }
    
    public function testMultipleChoiceField()
    {
        $f = new MultipleChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertEquals(array('IT'), $f->clean(array('IT')));
    }
    
    public function testMultipleChoiceField2()
    {
        $f = new MultipleChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertEquals(array('IT', 'Commercial'), $f->clean(array('IT', 'Commercial')));
    }
    
    public function testMultipleChoiceFieldInvalidListValidationError()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'Enter a list of values.');
        $f = new MultipleChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $f->clean('IT');
    }
    
    public function testFileFieldWithoutUploadedFile()
    {
        $f = new FileField();
        $this->assertEquals(null, $f->clean(null));
    }
    
    public function testFileFieldWithNotUploadedFile()
    {
        $this->setExpectedException('Stato\Webflow\Forms\ValidationError', 'No file was submitted.');
        $f = new FileField();
        $f->clean(new \Stato\Webflow\UploadedFile('/tmp/1234', 'test.jpg', 'image/jpeg', 123, 0));
    }
}