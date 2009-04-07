<?php

require_once dirname(__FILE__) . '/../../../tests/TestsHelper.php';

require_once 'helpers/string.php';
require_once 'form.php';

class Stato_FormErrorsTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->errors = new Stato_Form_Errors;
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
        $this->errors->setPrefix('contact');
        $this->assertEquals($html, $this->errors->__toString());
    }
}