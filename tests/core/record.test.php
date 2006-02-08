<?php

require_once(CORE_DIR.'/model/model.php');
require_once(TESTS_DIR.'/core/fixtures/models.php');

class SRecordTest extends UnitTestCase
{
    function testAttributeAccess()
    {
        $post = new Post();
        $post->title = 'Test Driven Developement';
        $this->assertEqual('Test Driven Developement', $post['title']);
        $this->assertEqual($post['title'], $post->title);
        $post['author'] = 'Goldoraf';
        $this->assertEqual('Goldoraf', $post->author);
        $this->assertEqual($post['author'], $post->author);
    }
    
    function testAttributeAccessOverloading()
    {
        $bill = new Bill();
        $bill->product = 'mouse';
        $bill->price = 100;
        $this->assertEqual(120, $bill->total);
        $bill->total = 200;
        $this->assertEqual(120, $bill->total);
    }
    
    function testMultiParamsAssignment()
    {
        $emp = new Employe(array('firstname'=>'Steve', 'lastname'=>'Warson', 
                                 'date_of_birth'=>array('year'=>'1962', 'month'=>'09', 'day'=>'12')));
        $this->assertIsA($emp->date_of_birth, 'SDate');
        $this->assertEqual('1962-09-12', $emp->date_of_birth->__toString());
    }
}

?>
