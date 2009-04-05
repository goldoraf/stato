<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

class ActiveRecordTest extends ActiveTestCase
{
    public $fixtures = array('posts', 'products', 'contracts', 'employes');
    public $models = array('user_with_serialization', 'boolean_false_by_default_post');
    
    public function test_attribute_access()
    {
        $post = new Post();
        $post->title = 'Test Driven Developement';
        $this->assertEquals('Test Driven Developement', $post['title']);
        $this->assertEquals($post['title'], $post->title);
        $post['author'] = 'Goldoraf';
        $this->assertEquals('Goldoraf', $post->author);
        $this->assertEquals($post['author'], $post->author);
    }
    
    public function test_attribute_access_overloading()
    {
        $bill = new Bill();
        $bill->product = 'mouse';
        $bill->price = 100;
        $this->assertEquals(120, $bill->total);
        $bill->total = 210;
        $this->assertEquals(175, $bill->price);
    }
    
    public function test_content_attributes_overloading()
    {
        $post = new Post();
        $this->assertEquals(array('title' => new SColumn('title', SColumn::STRING)), $post->content_attributes());
    }
    
    public function test_multi_params_assignment()
    {
        $emp = new Employe(array('firstname'=>'Steve', 'lastname'=>'Warson', 
                                 'date_of_birth'=>array('year'=>'1962', 'month'=>'09', 'day'=>'12')));
        $this->assertTrue($emp->date_of_birth instanceof SDate);
        $this->assertEquals('1962-09-12', $emp->date_of_birth->__toString());
    }
    
    public function test_serialized_attributes()
    {
        $user = new UserWithSerialization();
        $user->username = 'toto';
        $user->preferences = array('news' => 'left', 'max_friends' => 3, 'modules' => array(1,2,3));
        $this->assertEquals(
            array('news' => 'left', 'max_friends' => 3, 'modules' => array(1,2,3)),
            $user->preferences
        );
        $user->save();
        $user_reloaded = UserWithSerialization::$objects->get($user->id);
        $this->assertEquals(
            array('news' => 'left', 'max_friends' => 3, 'modules' => array(1,2,3)),
            $user_reloaded->preferences
        );
        $user = UserWithSerialization::$objects->create(array(
            'username' => 'toto',
            'preferences' => array('news' => 'left', 'max_friends' => 3, 'modules' => array(1,2,3))
        ));
        $this->assertEquals(
            array('news' => 'left', 'max_friends' => 3, 'modules' => array(1,2,3)),
            $user->preferences
        );
        $user_reloaded = UserWithSerialization::$objects->get($user->id);
        $this->assertEquals(
            array('news' => 'left', 'max_friends' => 3, 'modules' => array(1,2,3)),
            $user_reloaded->preferences
        );
    }
    
    public function test_is_new_record()
    {
        $post = new Post();
        $this->assertTrue($post->is_new_record());
        $post = new Post(array('id' => 200, 'title' => 'test'));
        $this->assertTrue($post->is_new_record());
        $post = new Post(array('id' => 1, 'title' => 'test'));
        $this->assertFalse($post->is_new_record());
    }
    
    public function test_set_attributes()
    {
        $post = Post::$objects->get(1);
        $post->title = 'Framework clone wars';
        $post->text  = 'bli bli bli';
        $post->save();
        $this->assertEquals('Framework clone wars', $post->title);
        $this->assertEquals('bli bli bli', $post->text);
        $post_bis = Post::$objects->get(1);
        $this->assertEquals($post->author, $post_bis->author);
    }
    
    public function test_null_values()
    {
        $emp = new Employe(array('firstname'=>'Steve'));
        $emp->save();
        $emp = Employe::$objects->get(3);
        $this->assertNull($emp->date_of_birth);
        $this->assertNull($emp->lastname);
    }
    
    public function test_read_write_boolean_attribute()
    {
        $post = Post::$objects->get(1);
        $this->assertTrue($post->published);
        $post->published = false;
        $this->assertFalse($post->published);
        $post->published = 'True';
        $this->assertTrue($post->published);
        $post->published = false;
        $post->save();
        $post_bis = Post::$objects->get(1);
        $this->assertFalse($post_bis->published);
        
        $post = new Post(array('title'=>'test', 'published'=>false));
        $this->assertFalse($post->published);
        $post->save();
        $this->assertFalse($post->published);
        $post_reloaded = Post::$objects->get($post->id);
        $this->assertFalse($post_reloaded->published);
    }
    
    public function test_boolean_false_by_default()
    {
        $post = new BooleanFalseByDefaultPost();
        $this->assertFalse($post->published);
        $post->title = 'test';
        $post->save();
        $this->assertFalse($post->published);
        $post_reloaded = BooleanFalseByDefaultPost::$objects->get($post->id);
        $this->assertFalse($post_reloaded->published);
        $post_reloaded->published = true;
        $this->assertTrue($post_reloaded->published);
        $post_reloaded->save();
        $post_reloaded2 = BooleanFalseByDefaultPost::$objects->get($post_reloaded->id);
        $this->assertTrue($post_reloaded2->published);
    }
    
    public function test_preserving_date_objects()
    {
        $contract = Contract::$objects->get(1);
        $this->assertTrue($contract->date instanceof SDate);
    }
    
    public function test_create()
    {
        $product = new Product();
        $product->name = 'DVD';
        $product->save();
        $this->assertEquals(2, $product->id);
        $product_reloaded = Product::$objects->get($product->id);
        $this->assertEquals('DVD', $product_reloaded->name);
    }
    
    public function test_update()
    {
        $product = new Product();
        $product->name = 'CD';
        $product->save();
        $product_reloaded = Product::$objects->get($product->id);
        $product_reloaded->name = 'CD-R';
        $product_reloaded->save();
        $product_reloaded_again = Product::$objects->get($product->id);
        $this->assertEquals('CD-R', $product_reloaded_again->name);
    }
    
    public function test_delete()
    {
        $product = new Product();
        $product->name = 'CD';
        $product->save();
        $product->delete();
        try { $p = Product::$objects->get($product->id); }
        catch (Exception $e) { }
        $this->assertEquals('SRecordNotFound', get_class($e));
    }
    
    public function test_reload()
    {
        $product = new Product();
        $product->name = 'CD';
        $product->save();
        $product->name = 'CD-R';
        $product->reload();
        $this->assertEquals('CD', $product->name);
    }
    
    public function test_save_with_timestamps()
    {
        $created_date = new SDateTime(2005,12,01,20,30,00);
        $post = Post::$objects->get(2);
        $this->assertTrue($post->created_on instanceof SDateTime);
        $this->assertEquals($created_date, $post->created_on);
        $this->assertEquals($post->created_on, $post->updated_on);
        $post->text = 'blo blo blo';
        $post->save();
        $this->assertNotEquals($post->created_on, $post->updated_on);
        
        $today = SDate::today();
        $new_post = new Post(array('title'=>'Timestamps and MySQL', 'author'=>'Goldoraf', 'text'=>'ttttt'));
        $this->assertNull($new_post->created_on);
        $this->assertNull($new_post->updated_on);
        $new_post->save();
        $this->assertTrue($new_post->created_on instanceof SDateTime);
        $this->assertTrue($new_post->created_on instanceof SDateTime);
        $this->assertEquals($new_post->created_on, $new_post->updated_on);
        $this->assertEquals($today->year, $new_post->created_on->year);
        $this->assertEquals($today->month, $new_post->created_on->month);
        $this->assertEquals($today->day, $new_post->created_on->day);
    }
}

