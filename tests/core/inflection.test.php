<?php

require_once(CORE_DIR.'/model/model.php');

// apparemment, la classe InflectionTest est déjà définie par SimpleTest
class InflectionClassTest extends UnitTestCase
{
    function InflectionClassTest()
    {
        $this->UnitTestCase('Inflection class tests');
    }
    
    function testPlural()
    {
        $this->assertEqual('products', Inflection::pluralize('product'));
    }
    
    function testSingular()
    {
        $this->assertEqual('product', Inflection::singularize('products'));
    }
}

?>
