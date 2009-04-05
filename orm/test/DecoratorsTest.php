<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class DummyDecorator extends SActiveRecordDecorator
{
    protected $test_arg = null;
    
    public function __construct($record, $test_arg)
    {
        $this->record = $record;
        $this->test_arg = $test_arg;
    }
    
    public function test_method()
    {
        return 1;
    }
}

class DecoratorsTest extends ActiveTestCase
{
    public $fixtures = array('posts', 'companies', 'products');
    
    function test_basics()
    {
        $post = new DummyDecorator(Post::$objects->get(1), 'test');
        $this->assertEquals('Frameworks : A new hope...', $post->title);
        $this->assertEquals(1, $post->test_method());
        $post->title = 'Framework clone wars';
        $this->assertEquals('Framework clone wars', $post->title);
        $post->save();
        $post_bis = new DummyDecorator(Post::$objects->get(1), 'test');
        $this->assertEquals($post->title, $post_bis->title);
        $this->assertEquals($post->author, $post_bis->author);
    }
    
    function test_associations()
    {
        $company = new DummyDecorator(Company::$objects->get(1), 'test');
        $this->assertEquals(1, $company->products->count());
        $product = new Product(array('name'=>'CD-R', 'price'=>'0.75'));
        $company->products->add($product);
        $this->assertEquals(2, $company->products->count());
        $company->save();
        $company_reloaded = new DummyDecorator(Company::$objects->get(1), 'test');
        $this->assertEquals(2, $company_reloaded->products->count());
    }
}

