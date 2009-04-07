<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

class SFormErrorsTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->errors = new SFormErrors;
        $this->errors['subject'] = 'This field is required.';
        $this->errors['email'] = 'Enter a valid value.';
    }
    
    public function test_rendering()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="subject">Subject</label> - This field is required.</li>
<li><label for="email">Email</label> - Enter a valid value.</li>
</ul>
EOT;
        $this->assertEquals($html, $this->errors->__toString());
    }
    
    public function test_rendering_with_prefix()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="contact_subject">Subject</label> - This field is required.</li>
<li><label for="contact_email">Email</label> - Enter a valid value.</li>
</ul>
EOT;
        $this->errors->set_prefix('contact');
        $this->assertEquals($html, $this->errors->__toString());
    }
}