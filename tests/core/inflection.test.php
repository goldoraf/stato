<?php

require_once(CORE_DIR.'/model/model.php');

class SInflectionTest extends UnitTestCase
{
    function testPlural()
    {
        $this->assertEqual('products', SInflection::pluralize('product'));
    }
    
    function testSingular()
    {
        $this->assertEqual('product', SInflection::singularize('products'));
    }
}

?>
