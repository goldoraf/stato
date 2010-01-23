<?php





require_once dirname(__FILE__) . '/../../TestsHelper.php';

class Stato_Webflow_Forms_FormTest extends Stato_Webflow_TestCase
{
    public function setup()
    {
        if (!function_exists('__')) {
            eval('function __($key, $options = array()) {
                return $key;
            }');
        }
        $this->form = new Stato_Webflow_Forms_Form;
        $this->form->title = new Stato_Webflow_Forms_CharField;
        $this->form->body = new Stato_Webflow_Forms_TextField;
    }
    
    public function testAddField()
    {
        $this->form->addField('author', 'char');
        $this->assertTrue(isset($this->form->author));
    }
    
    public function testIsMultipart()
    {
        $this->assertFalse($this->form->isMultipart());
        $this->form->file = new Stato_Webflow_Forms_FileField;
        $this->assertTrue($this->form->isMultipart());
    }
    
    public function testBindField()
    {
        $this->assertTrue(isset($this->form->title));
        $this->assertTrue($this->form->title instanceof Stato_Webflow_Forms_BoundField);
    }
    
    public function testGetNotSetFieldShouldThrow()
    {
        $this->setExpectedException('Stato_Webflow_Forms_Exception');
        $foo = $this->form->foo;
    }
    
    public function testBindFieldBasicRendering()
    {
        $this->assertEquals('<input type="text" name="title" id="title" />', $this->form->title->render());
        $this->assertEquals('<label for="title">Title</label>', $this->form->title->labelTag);
    }
    
    public function testFieldRenderingWithFormPrefix()
    {
        $this->form->setPrefix('post');
        $this->assertEquals('<input type="text" name="post[title]" id="post_title" />', $this->form->title->render());
        $this->assertEquals('<label for="post_title">Title</label>', $this->form->title->labelTag);
    }
    
    public function testFieldRenderingWithInitialValue()
    {
        $this->form->author = new Stato_Webflow_Forms_CharField(array('initial' => 'Enter your name.'));
        $this->assertEquals('<input type="text" name="author" id="author" value="Enter your name." />', 
                            $this->form->author->render());
        $this->assertEquals('<label for="author">Author</label>', $this->form->author->labelTag);
    }
    
    public function testFieldRenderingWithFormWithInitialValues()
    {
        $this->form->author = new Stato_Webflow_Forms_CharField();
        $this->form->setInitialValues(array('author' => 'Enter your name.'));
        $this->assertEquals('<input type="text" name="author" id="author" value="Enter your name." />', 
                            $this->form->author->render());
        $this->assertEquals('<label for="author">Author</label>', $this->form->author->labelTag);
    }
    
    public function testFieldRenderingWithBoundFormButNoValue()
    {
        $this->form->author = new Stato_Webflow_Forms_CharField(array('initial' => 'Enter your name.'));
        $this->form->isValid(array());
        $this->assertEquals('<input type="text" name="author" id="author" />', $this->form->author->render());
        $this->assertEquals('<label for="author">Author</label>', $this->form->author->labelTag);
    }
    
    public function testFieldRenderingWithBoundFormAndCleanedValue()
    {
        $this->form->author = new Stato_Webflow_Forms_CharField(array('initial' => 'Enter your name.'));
        $this->form->isValid(array('author' => 'admin'));
        $this->assertEquals('<input type="text" name="author" id="author" value="admin" />', $this->form->author->render());
        $this->assertEquals('<label for="author">Author</label>', $this->form->author->labelTag);
    }
    
    public function testFormBasicRendering()
    {
        $html = <<<EOT
<p><label for="title">Title</label><input type="text" name="title" id="title" /></p>
<p><label for="body">Body</label><textarea name="body" cols="40" rows="10" id="body"></textarea></p>
EOT;
        $this->assertEquals($html, $this->form->render());
    }
    
    public function testFormBasicRenderingWithHiddenField()
    {
        $html = <<<EOT
<p><label for="title">Title</label><input type="text" name="title" id="title" /></p>
<p><label for="body">Body</label><textarea name="body" cols="40" rows="10" id="body"></textarea></p>
<input type="hidden" name="author_id" id="author_id" />
EOT;
        $this->form->author_id = new Stato_Webflow_Forms_IntegerField(array('input' => new Stato_Webflow_Forms_HiddenInput));
        $this->assertEquals($html, $this->form->render());
    }
    
    public function testFormBasicRenderingWithError()
    {
        $html = <<<EOT
<p><label for="title">Title</label><input type="text" name="title" id="title" /></p>
<p><label for="body">Body</label><textarea name="body" cols="40" rows="10" id="body"></textarea></p>
<p><label for="author">Author</label><input type="text" name="author" id="author" /><span class="error">This field is required.</span></p>
EOT;
        $this->form->author = new Stato_Webflow_Forms_CharField(array('required' => true));
        $this->form->isValid(array());
        $this->assertEquals($html, $this->form->render());
    }
    
    public function testIsValidWithoutConstraints()
    {
        $this->assertTrue($this->form->isValid(array('title' => 'foo')));
    }
    
    public function testIsValidWithoutConstraintsAndWithoutData()
    {
        $this->assertTrue($this->form->isValid(array()));
    }
    
    public function testIsValidWithRequiredField()
    {
        $this->form->author = new Stato_Webflow_Forms_CharField(array('required' => true));
        $this->assertTrue($this->form->isValid(array('author' => 'admin')));
    }
    
    public function testIsValidWithRequiredFieldNotProvided()
    {
        $this->form->author = new Stato_Webflow_Forms_CharField(array('required' => true));
        $this->assertFalse($this->form->isValid(array()));
        $this->assertEquals('This field is required.', current($this->form->errors));
    }
    
    public function testIsValidWithVariousConstraints()
    {
        $form = new Stato_Webflow_Forms_Form;
        $form->to = new Stato_Webflow_Forms_EmailField(array('required' => true));
        $form->cc = new Stato_Webflow_Forms_EmailField();
        $form->subject = new Stato_Webflow_Forms_CharField(array('min_length' => 5));
        $form->body = new Stato_Webflow_Forms_TextField;
        $form2 = clone $form;
        $this->assertFalse($form->isValid(array('cc' => '<xss></xss>john@', 'subject' => 'doh', 'body' => 'hello')));
        $this->assertEquals(array('to' => null, 'cc' => 'xssxssjohn@', 'subject' => 'doh', 'body' => 'hello'),
                            $form->getCleanedData());
        $this->assertEquals('This field is required.', $form->errors['to']);
        $this->assertEquals('Enter a valid e-mail address.', $form->errors['cc']);
        $this->assertEquals('Ensure this value has at least 5 characters (it has 3).', $form->errors['subject']);
        
        $this->assertTrue($form2->isValid(array('to' => 'jane@doe.net', 'cc' => 'john@doe.net', 
                                                'subject' => 'donuts', 'body' => 'hello')));
        $this->assertEquals(array('to' => 'jane@doe.net', 'cc' => 'john@doe.net', 
                                  'subject' => 'donuts', 'body' => 'hello'), $form2->getCleanedData());
    }
    
    public function testIsValidWithCleanHook()
    {
        require_once dirname(__FILE__) . '/../files/forms/test_form_1.php';
        $form = new TestForm1;
        $this->assertFalse($form->isValid(array('lib' => 'bar')));
        $this->assertEquals('Lib should be "foo"', $form->errors['_all_']);
        $form = new TestForm1;
        $this->assertTrue($form->isValid(array('lib' => 'foo')));
    }
    
    public function testIsValidWithCleanFieldHook()
    {
        require_once dirname(__FILE__) . '/../files/forms/test_form_2.php';
        $form = new TestForm2;
        $this->assertFalse($form->isValid(array('lib' => 'bar')));
        $this->assertEquals('Lib should be "foo"', $form->errors['lib']);
        $form = new TestForm2;
        $this->assertTrue($form->isValid(array('lib' => 'foo')));
    }
}