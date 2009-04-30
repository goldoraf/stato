<?php

namespace Stato\Webflow\Forms;

use Stato\Webflow\TestCase;

require_once __DIR__ . '/../../TestsHelper.php';

class ErrorsTest extends TestCase
{
    public function setup()
    {
        if (!function_exists('\__')) {
            eval('function __($key, $options = array()) {
                return $key;
            }');
        }
        $this->form = new Form;
        $this->form->subject = new CharField;
        $this->form->email = new EmailField;
        $this->errors = new Errors($this->form);
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
        $this->form->content = new TextField(array('label' => 'Body'));
        $this->errors['content'] = 'Please enter something.';
        $this->assertEquals($html, $this->errors->__toString());
    }
}