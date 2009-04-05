<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

class AjaxHelperTest extends StatoTestCase
{
    public function test_ajax()
    {
        $this->assertDomEquals(
            link_to_function('Hello', "alert('Hello World')"), 
            '<a href="#" onclick="alert(\'Hello World\'); return false;">Hello</a>'
        );
    }
}

