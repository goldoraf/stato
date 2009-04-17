<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

class SFormFieldTest extends PHPUnit_Framework_TestCase
{
    public function test_field_clean()
    {
        $f = new SField();
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function test_required_field()
    {
        $this->setExpectedException('SValidationError');
        $f = new SField(array('required' => true));
        $f->clean('');
    }
    
    public function test_field_with_custom_error_message()
    {
        $this->setExpectedException('SValidationError', 'Plz give me something!');
        $f = new SField(array('required' => true, 'error_messages' => array('required' => 'Plz give me something!')));
        $f->clean('');
    }
    
    public function test_field_render()
    {
        $f = new SField();
        $this->assertEquals('<input type="text" name="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function test_field_render_with_html_attrs()
    {
        $f = new SField();
        $this->assertEquals('<input type="text" name="foo" id="id_foo" value="bar" />', $f->render('foo', 'bar', array('id' => 'id_foo')));
    }
    
    public function test_field_bind()
    {
        $f = new SField();
        $this->assertEquals('<input type="text" name="foo" value="bar" />', $f->bind('foo', 'bar')->__toString());
    }
    
    public function test_field_with_input_option()
    {
        $f = new SField(array('input' => 'SHiddenInput'));
        $this->assertEquals('<input type="hidden" name="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function test_field_with_input_attrs_option()
    {
        $f = new SField(array('input_attrs' => array('class' => 'foo')));
        $this->assertEquals('<input type="text" name="foo" class="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function test_field_with_bad_input_option_should_throw()
    {
        $this->setExpectedException('Exception');
        $f = new SField(array('input' => 'SRequest'));
    }
    
    public function test_char_field_with_length_option()
    {
        $f = new SCharField(array('length' => 3));
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function test_char_field_with_max_length_option()
    {
        $f = new SCharField(array('max_length' => 6));
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function test_char_field_with_min_length_option()
    {
        $f = new SCharField(array('min_length' => 2));
        $this->assertEquals('bar', $f->clean('bar'));
    }
    
    public function test_char_field_with_min_length_option_render()
    {
        $f = new SCharField(array('min_length' => 2));
        $this->assertEquals('<input type="text" name="foo" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function test_char_field_with_max_length_option_render()
    {
        $f = new SCharField(array('max_length' => 6));
        $this->assertEquals('<input type="text" name="foo" maxlength="6" value="bar" />', $f->render('foo', 'bar'));
    }
    
    public function test_char_field_length_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Ensure this value has %d characters (it has %d).');
        $f = new SCharField(array('length' => 8));
        $f->clean('bar');
    }
    
    public function test_char_field_max_length_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Ensure this value has at most %d characters (it has %d).');
        $f = new SCharField(array('max_length' => 8));
        $f->clean('barbarbar');
    }
    
    public function test_char_field_min_length_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Ensure this value has at least %d characters (it has %d).');
        $f = new SCharField(array('min_length' => 4));
        $f->clean('bar');
    }
    
    public function test_char_field_with_regex_option()
    {
        $f = new SCharField(array('regex' => '/^a/'));
        $this->assertEquals('abc', $f->clean('abc'));
    }
    
    public function test_char_field_with_regex_option_validation_error()
    {
        $this->setExpectedException('SValidationError');
        $f = new SCharField(array('regex' => '/^a/'));
        $f->clean('bc');
    }
    
    public function test_char_field_validation_error_should_sanitize()
    {
        try {
            $f = new SCharField(array('regex' => '/^a/'));
            $f->clean('<xss></xss>bc');
        } catch (SValidationError $e) {
            $this->assertEquals('bc', $e->get_cleaned_value());
        }
    }
    
    public function test_text_field_with_input_attrs_option()
    {
        $f = new STextField(array('input_attrs' => array('cols' => 80, 'rows' => 20)));
        $this->assertEquals('<textarea name="bar" cols="80" rows="20"></textarea>', $f->render('bar'));
    }
    
    public function test_integer_field()
    {
        $f = new SIntegerField();
        $this->assertEquals(3, $f->clean('3'));
    }
    
    public function test_integer_field_with_max_value_option()
    {
        $f = new SIntegerField(array('max_value' => 6));
        $this->assertEquals(5, $f->clean('5'));
    }
    
    public function test_integer_field_with_min_value_option()
    {
        $f = new SIntegerField(array('min_value' => 2));
        $this->assertEquals(5, $f->clean('5'));
    }
    
    public function test_integer_field_max_value_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Ensure this value is greater than or equal to %s.');
        $f = new SIntegerField(array('max_value' => 8));
        $f->clean('12');
    }
    
    public function test_integer_field_min_value_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Ensure this value is less than or equal to %s.');
        $f = new SIntegerField(array('min_value' => 4));
        $f->clean('3');
    }
    
    public function test_date_time_field()
    {
        $f = new SDateTimeField();
        $this->assertEquals(new DateTime('2009-03-28'), $f->clean('2009-03-28'));
    }
    
    public function test_date_time_field_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Enter a valid date.');
        $f = new SDateTimeField();
        $f->clean('2009-03-32');
    }
    
    public function test_email_field()
    {
        $f = new SEmailField();
        $this->assertEquals('john@doe.net', $f->clean('john@doe.net'));
    }
    
    public function test_email_field_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Enter a valid e-mail address.');
        $f = new SEmailField();
        $f->clean('john');
    }
    
    public function test_url_field()
    {
        $f = new SUrlField();
        $this->assertEquals('http://stato-framework.org', $f->clean('http://stato-framework.org'));
    }
    
    public function test_url_field_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Enter a valid URL.');
        $f = new SUrlField();
        $f->clean('http:/stato-framework.org');
    }
    
    public function test_ip_field()
    {
        $f = new SIpField();
        $this->assertEquals('127.0.0.1', $f->clean('127.0.0.1'));
    }
    
    public function test_ip_field_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Enter a valid IP.');
        $f = new SIpField();
        $f->clean('127.0.0.1.1');
    }
    
    public function test_boolean_field_with_true_values()
    {
        $f = new SBooleanField();
        $this->assertTrue($f->clean('true'));
        $this->assertTrue($f->clean('on'));
        $this->assertTrue($f->clean('yes'));
        $this->assertTrue($f->clean('1'));
    }
    
    public function test_boolean_field_with_false_values()
    {
        $f = new SBooleanField();
        $this->assertFalse($f->clean('false'));
        $this->assertFalse($f->clean('off'));
        $this->assertFalse($f->clean('no'));
        $this->assertFalse($f->clean('0'));
    }
    
    public function test_boolean_field_with_not_boolean_value()
    {
        $f = new SBooleanField();
        $this->assertFalse($f->clean('foo'));
    }
    
    public function test_required_boolean_field()
    {
        $f = new SBooleanField(array('required' => true));
        $this->assertTrue($f->clean('1'));
    }
    
    public function test_required_boolean_field_with_false_value()
    {
        $this->setExpectedException('SValidationError');
        $f = new SBooleanField(array('required' => true));
        $f->clean('0');
    }
    
    public function test_boolean_field_render()
    {
        $f = new SBooleanField();
        $this->assertEquals('<input type="hidden" name="foo" value="0" /><input type="checkbox" name="foo" value="1" />',
                            $f->render('foo'));
    }
    
    public function test_boolean_field_render_with_value_options()
    {
        $f = new SBooleanField(array('checked_value' => 'true', 'unchecked_value' => 'false'));
        $this->assertEquals('<input type="hidden" name="foo" value="false" /><input type="checkbox" name="foo" value="true" />',
                            $f->render('foo'));
    }
    
    public function test_boolean_field_render_checked()
    {
        $f = new SBooleanField();
        $this->assertEquals('<input type="hidden" name="foo" value="0" /><input type="checkbox" name="foo" checked="checked" value="1" />',
                            $f->render('foo', true));
    }
    
    public function test_choice_field()
    {
        $f = new SChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertEquals('IT', $f->clean('IT'));
    }
    
    public function test_choice_field_with_associative_array()
    {
        $f = new SChoiceField(array('choices' => array(1=>'Marketing', 2=>'IT', 3=>'Commercial')));
        $this->assertEquals('2', $f->clean('2'));
    }
    
    public function test_choice_field_with_multidimensional_array()
    {
        $f = new SChoiceField(array('choices' => array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        )));
        $this->assertEquals('PHP', $f->clean('PHP'));
    }
    
    public function test_choice_field_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Select a valid choice.');
        $f = new SChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $f->clean('HR');
    }
    
    public function test_choice_field_with_associative_array_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Select a valid choice.');
        $f = new SChoiceField(array('choices' => array(1=>'Marketing', 2=>'IT', 3=>'Commercial')));
        $f->clean('4');
    }
    
    public function test_choice_field_with_multidimensional_array_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Select a valid choice.');
        $f = new SChoiceField(array('choices' => array(
            'languages' => array('PHP', 'Python', 'Ruby'),
            'os' => array('Linux', 'MacOS', 'Windows')
        )));
        $f->clean('Java');
    }
    
    public function test_multiple_choice_field()
    {
        $f = new SMultipleChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertEquals(array('IT'), $f->clean(array('IT')));
    }
    
    public function test_multiple_choice_field2()
    {
        $f = new SMultipleChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $this->assertEquals(array('IT', 'Commercial'), $f->clean(array('IT', 'Commercial')));
    }
    
    public function test_multiple_choice_field_invalid_list_validation_error()
    {
        $this->setExpectedException('SValidationError', 'Enter a list of values.');
        $f = new SMultipleChoiceField(array('choices' => array('Marketing', 'IT', 'Commercial')));
        $f->clean('IT');
    }
    
    public function test_file_field_without_uploaded_file()
    {
        $f = new SFileField();
        $this->assertEquals(null, $f->clean(null));
    }
    
    public function test_file_field_with_not_uploaded_file()
    {
        $this->setExpectedException('SValidationError', 'No file was submitted.');
        $f = new SFileField();
        $f->clean(new SUploadedFile('/tmp/1234', 'test.jpg', 'image/jpeg', 123, 0));
    }
}