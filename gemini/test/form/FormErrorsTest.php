<?php

require_once dirname(__FILE__) . '/../../../test/tests_helper.php';

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
<li>This field is required.</li>
<li>Enter a valid value.</li>
</ul>
EOT;
        $this->assertEquals($html, $this->errors->__toString());
    }
}