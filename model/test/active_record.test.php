<?php

class ActiveRecordTest extends ActiveTestCase
{
    public $fixtures = array('posts', 'products', 'contracts', 'employes');
    
    public function testAttributeAccess()
    {
        $post = new Post();
        $post->title = 'Test Driven Developement';
        $this->assertEqual('Test Driven Developement', $post['title']);
        $this->assertEqual($post['title'], $post->title);
        $post['author'] = 'Goldoraf';
        $this->assertEqual('Goldoraf', $post->author);
        $this->assertEqual($post['author'], $post->author);
    }
    
    public function testAttributeAccessOverloading()
    {
        $bill = new Bill();
        $bill->product = 'mouse';
        $bill->price = 100;
        $this->assertEqual(120, $bill->total);
        $bill->total = 210;
        $this->assertEqual(175, $bill->price);
    }
    
    public function testMultiParamsAssignment()
    {
        $emp = new Employe(array('firstname'=>'Steve', 'lastname'=>'Warson', 
                                 'date_of_birth'=>array('year'=>'1962', 'month'=>'09', 'day'=>'12')));
        $this->assertIsA($emp->date_of_birth, 'SDate');
        $this->assertEqual('1962-09-12', $emp->date_of_birth->__toString());
    }
    
    public function testIsNewRecord()
    {
        $post = new Post();
        $this->assertTrue($post->isNewRecord());
        $post = new Post(array('id' => 200, 'title' => 'test'));
        $this->assertTrue($post->isNewRecord());
        $post = new Post(array('id' => 1, 'title' => 'test'));
        $this->assertFalse($post->isNewRecord());
    }
    
    public function testSetAttributes()
    {
        $post = Post::$objects->get(1);
        $post->title = 'Framework clone wars';
        $post->text  = 'bli bli bli';
        $post->save();
        $this->assertEqual('Framework clone wars', $post->title);
        $this->assertEqual('bli bli bli', $post->text);
        $postBis = Post::$objects->get(1);
        $this->assertEqual($post->author, $postBis->author);
    }
    
    public function testNullValues()
    {
        $emp = new Employe(array('firstname'=>'Steve'));
        $emp->save();
        $emp = Employe::$objects->get(3);
        $this->assertNull($emp->date_of_birth);
        $this->assertNull($emp->lastname);
    }
    
    public function testReadWriteBooleanAttribute()
    {
        $post = Post::$objects->get(1);
        $this->assertTrue($post->published);
        $post->published = False;
        $this->assertFalse($post->published);
        $post->published = 'True';
        $this->assertTrue($post->published);
        $post->published = False;
        $post->save();
        $postBis = Post::$objects->get(1);
        $this->assertFalse($postBis->published);
    }
    
    public function testPreservingDateObjects()
    {
        $contract = Contract::$objects->get(1);
        $this->assertIsA($contract->date, 'SDate');
    }
    
    public function testCreate()
    {
        $product = new Product();
        $product->name = 'DVD';
        $product->save();
        $this->assertEqual(2, $product->id);
        $productReloaded = Product::$objects->get($product->id);
        $this->assertEqual('DVD', $productReloaded->name);
    }
    
    public function testUpdate()
    {
        $product = new Product();
        $product->name = 'CD';
        $product->save();
        $productReloaded = Product::$objects->get($product->id);
        $productReloaded->name = 'CD-R';
        $productReloaded->save();
        $productReloadedAgain = Product::$objects->get($product->id);
        $this->assertEqual('CD-R', $productReloadedAgain->name);
    }
    
    public function testDelete()
    {
        $product = new Product();
        $product->name = 'CD';
        $product->save();
        $product->delete();
        try { $p = Product::$objects->get($product->id); }
        catch (Exception $e) { }
        $this->assertEqual('SActiveRecordDoesNotExist', get_class($e));
    }
    
    public function testSaveWithTimestamps()
    {
        $created_date = new SDateTime(2005,12,01,20,30,00);
        $post = Post::$objects->get(2);
        $this->assertIsA($post->created_on, 'SDateTime');
        $this->assertEqual($created_date, $post->created_on);
        $this->assertEqual($post->created_on, $post->updated_on);
        $post->text = 'blo blo blo';
        $post->save();
        $this->assertNotEqual($post->created_on, $post->updated_on);
        
        $today = SDate::today();
        $new_post = new Post(array('title'=>'Timestamps and MySQL', 'author'=>'Goldoraf', 'text'=>'ttttt'));
        $this->assertNull($new_post->created_on);
        $this->assertNull($new_post->updated_on);
        $new_post->save();
        $this->assertIsA($new_post->created_on, 'SDateTime');
        $this->assertIsA($new_post->created_on, 'SDateTime');
        $this->assertEqual($new_post->created_on, $new_post->updated_on);
        $this->assertEqual($today->year, $new_post->created_on->year);
        $this->assertEqual($today->month, $new_post->created_on->month);
        $this->assertEqual($today->day, $new_post->created_on->day);
    }
}

?>
