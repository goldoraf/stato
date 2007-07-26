<?php

class AjaxHelperTest extends HelperTestCase
{
    public function test_ajax()
    {
        $this->assertDomEqual(
            link_to_function('Hello', "alert('Hello World')"), 
            '<a href="#" onclick="alert(\'Hello World\'); return false;">Hello</a>'
        );
    }
}

?>