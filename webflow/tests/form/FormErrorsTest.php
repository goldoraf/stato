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
<li>This field is required.</li>
<li>Enter a valid value.</li>
</ul>
EOT;
        $this->assertEquals($html, $this->errors->__toString());
    }
}