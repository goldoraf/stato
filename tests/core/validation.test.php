<?php

require_once(CORE_DIR.'/model/model.php');

class InflectionTest extends UnitTestCase
{
    function InflectionTest()
    {
        $this->UnitTestCase('Inflection class tests');
    }
    
    function testPlural()
    {
        $this->assertEqual('products', SInflection::pluralize('product'));
    }
    
    function testSingular()
    {
        
    }
}

?>
