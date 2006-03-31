<?php

require_once(CORE_DIR.'/view/view.php');

class AjaxHelperTest extends HelperTestCase
{
    public function testAjax()
    {
        $this->assertDomEqual(
            link_to_function('Hello', "alert('Hello World')"), 
            '<a href="#" onclick="alert(\'Hello World\'); return false;">Hello</a>'
        );
    }
}

?>
