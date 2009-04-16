<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

if (!function_exists('__')) {
    function __($key, $options = array()) {
        return $key;
    }
}

class SFormTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->form = new SForm;
        $this->form->title = new SCharField;
        $this->form->body = new STextField;
    }
    
    public function test_add_field()
    {
        $this->form->add_field('author', 'char');
        $this->assertTrue(isset($this->form->author));
    }
    
    public function test_is_multipart()
    {
        $this->assertFalse($this->form->is_multipart());
        $this->form->file = new SFileField;
        $this->assertTrue($this->form->is_multipart());
    }
    
    public function test_bind_field()
    {
        $this->assertTrue(isset($this->form->title));
        $this->assertTrue($this->form->title instanceof SBoundField);
    }
    
    public function test_get_not_set_field_should_throw()
    {
        $this->setExpectedException('SFormException');
        $foo = $this->form->foo;
    }
    
    public function test_bind_field_basic_rendering()
    {
        $this->assertEquals('<input type="text" name="title" id="title" />', $this->form->title->render());
        $this->assertEquals('<label for="title">Title</label>', $this->form->title->label_tag);
    }
    
    public function test_field_rendering_with_form_prefix()
    {
        $this->form->set_prefix('post');
        $this->assertEquals('<input type="text" name="post[title]" id="post_title" />', $this->form->title->render());
        $this->assertEquals('<label for="post_title">Title</label>', $this->form->title->label_tag);
    }
    
    public function test_field_rendering_with_initial_value()
    {
        $this->form->author = new SCharField(array('initial' => 'Enter your name.'));
        $this->assertEquals('<input type="text" name="author" id="author" value="Enter your name." />', 
                            $this->form->author->render());
        $this->assertEquals('<label for="author">Author</label>', $this->form->author->label_tag);
    }
    
    public function test_field_rendering_with_form_with_initial_values()
    {
        $this->form->author = new SCharField();
        $this->form->set_initial_values(array('author' => 'Enter your name.'));
        $this->assertEquals('<input type="text" name="author" id="author" value="Enter your name." />', 
                            $this->form->author->render());
        $this->assertEquals('<label for="author">Author</label>', $this->form->author->label_tag);
    }
    
    public function test_field_rendering_with_bound_form_but_no_value()
    {
        $this->form->author = new SCharField(array('initial' => 'Enter your name.'));
        $this->form->is_valid(array());
        $this->assertEquals('<input type="text" name="author" id="author" />', $this->form->author->render());
        $this->assertEquals('<label for="author">Author</label>', $this->form->author->label_tag);
    }
    
    public function test_field_rendering_with_bound_form_and_cleaned_value()
    {
        $this->form->author = new SCharField(array('initial' => 'Enter your name.'));
        $this->form->is_valid(array('author' => 'admin'));
        $this->assertEquals('<input type="text" name="author" id="author" value="admin" />', $this->form->author->render());
        $this->assertEquals('<label for="author">Author</label>', $this->form->author->label_tag);
    }
    
    public function test_form_basic_rendering()
    {
        $html = <<<EOT
<p><label for="title">Title</label><input type="text" name="title" id="title" /></p>
<p><label for="body">Body</label><textarea name="body" cols="40" rows="10" id="body"></textarea></p>
EOT;
        $this->assertEquals($html, $this->form->render());
    }
    
    public function test_form_basic_rendering_with_error()
    {
        $html = <<<EOT
<p><label for="title">Title</label><input type="text" name="title" id="title" /></p>
<p><label for="body">Body</label><textarea name="body" cols="40" rows="10" id="body"></textarea></p>
<p><label for="author">Author</label><input type="text" name="author" id="author" /><span class="error">This field is required.</span></p>
EOT;
        $this->form->author = new SCharField(array('required' => true));
        $this->form->is_valid(array());
        $this->assertEquals($html, $this->form->render());
    }
    
    public function test_is_valid_without_constraints()
    {
        $this->assertTrue($this->form->is_valid(array('title' => 'foo')));
    }
    
    public function test_is_valid_without_constraints_and_without_data()
    {
        $this->assertTrue($this->form->is_valid(array()));
    }
    
    public function test_is_valid_with_required_field()
    {
        $this->form->author = new SCharField(array('required' => true));
        $this->assertTrue($this->form->is_valid(array('author' => 'admin')));
    }
    
    public function test_is_valid_with_required_field_not_provided()
    {
        $this->form->author = new SCharField(array('required' => true));
        $this->assertFalse($this->form->is_valid(array()));
        $this->assertEquals('This field is required.', current($this->form->errors));
    }
    
    public function test_is_valid_with_various_constraints()
    {
        $form = new SForm;
        $form->to = new SEmailField(array('required' => true));
        $form->cc = new SEmailField();
        $form->subject = new SCharField(array('min_length' => 5));
        $form->body = new STextField;
        $form2 = clone $form;
        $this->assertFalse($form->is_valid(array('cc' => '<xss></xss>john@', 'subject' => 'doh', 'body' => 'hello')));
        $this->assertEquals(array('to' => null, 'cc' => 'xss/xssjohn@', 'subject' => 'doh', 'body' => 'hello'),
                            $form->get_cleaned_data());
        $this->assertEquals('This field is required.', $form->errors['to']);
        $this->assertEquals('Enter a valid e-mail address.', $form->errors['cc']);
        $this->assertEquals('Ensure this value has at least 5 characters (it has 3).', $form->errors['subject']);
        
        $this->assertTrue($form2->is_valid(array('to' => 'jane@doe.net', 'cc' => 'john@doe.net', 
                                                'subject' => 'donuts', 'body' => 'hello')));
        $this->assertEquals(array('to' => 'jane@doe.net', 'cc' => 'john@doe.net', 
                                  'subject' => 'donuts', 'body' => 'hello'), $form2->get_cleaned_data());
    }
    
    public function test_is_valid_with_clean_hook()
    {
        require_once 'files/forms/test_form_1.php';
        $form = new TestForm1;
        $this->assertFalse($form->is_valid(array('lib' => 'bar')));
        $this->assertEquals('Lib should be "foo"', $form->errors['_all_']);
        $form = new TestForm1;
        $this->assertTrue($form->is_valid(array('lib' => 'foo')));
    }
    
    public function test_is_valid_with_clean_field_hook()
    {
        require_once 'files/forms/test_form_2.php';
        $form = new TestForm2;
        $this->assertFalse($form->is_valid(array('lib' => 'bar')));
        $this->assertEquals('Lib should be "foo"', $form->errors['lib']);
        $form = new TestForm2;
        $this->assertTrue($form->is_valid(array('lib' => 'foo')));
    }
}