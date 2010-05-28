<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

class SFormErrorsTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->errors = new SFormErrors;
        $this->errors->add_error('subject', 'This field is required.');
        $this->errors->add_error('email', 'Enter a valid value.', '@');
    }
    
    public function test_rendering()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="subject">Subject</label>This field is required.</li>
<li><label for="email">@</label>Enter a valid value.</li>
</ul>
EOT;
        $this->assertEquals($html, $this->errors->__toString());
    }
    
    public function test_rendering_with_single_prefix()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="contact_subject">Subject</label>This field is required.</li>
<li><label for="contact_email">@</label>Enter a valid value.</li>
</ul>
EOT;
        $this->errors->set_prefix('contact');
        $this->assertEquals($html, $this->errors->__toString());
    }
    
    public function test_rendering_with_multiple_prefix()
    {
        $html = <<<EOT
<ul class="errorlist">
<li><label for="contact_details_subject">Subject</label>This field is required.</li>
<li><label for="contact_details_email">@</label>Enter a valid value.</li>
</ul>
EOT;
        $this->errors->set_prefix(array('contact', 'details'));
        $this->assertEquals($html, $this->errors->__toString());
    }
}