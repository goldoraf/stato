<?php

require_once dirname(__FILE__) . '/../../../tests/TestsHelper.php';

require_once 'helpers/string.php';
require_once 'form.php';

class Stato_FormErrorsTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->form = new Stato_Form;
        $this->form->subject = new Stato_Form_CharField;
        $this->form->email = new Stato_Form_EmailField;
        $this->errors = new Stato_Form_Errors($this->form);
        $this->errors['subject'] = 'This field is required.';
        $this->errors['email'] = 'Enter a valid value.';
    }
    
    public function testRendering()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="subject">Subject</label> - This field is required.</li>
<li><label for="email">Email</label> - Enter a valid value.</li>
</ul>
EOT;
        $this->assertEquals($html, $this->errors->__toString());
    }
    
    public function testRenderingWithPrefix()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="contact_subject">Subject</label> - This field is required.</li>
<li><label for="contact_email">Email</label> - Enter a valid value.</li>
</ul>
EOT;
        $this->form->setPrefix('contact');
        $this->assertEquals($html, $this->errors->__toString());
    }
    
    public function testRenderingWithLabeledField()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="subject">Subject</label> - This field is required.</li>
<li><label for="email">Email</label> - Enter a valid value.</li>
<li><label for="content">Body</label> - Please enter something.</li>
</ul>
EOT;
        $this->form->content = new Stato_Form_TextField(array('label' => 'Body'));
        $this->errors['content'] = 'Please enter something.';
        $this->assertEquals($html, $this->errors->__toString());
    }
}