<?php

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
        $this->assertEqual('Frameworks : A new hope...', $post->title);
        $this->assertEqual(1, $post->test_method());
        $post->title = 'Framework clone wars';
        $this->assertEqual('Framework clone wars', $post->title);
        $post->save();
        $post_bis = new DummyDecorator(Post::$objects->get(1), 'test');
        $this->assertEqual($post->title, $post_bis->title);
        $this->assertEqual($post->author, $post_bis->author);
    }
    
    function test_associations()
    {
        $company = new DummyDecorator(Company::$objects->get(1), 'test');
        $this->assertEqual(1, $company->products->count());
        $product = new Product(array('name'=>'CD-R', 'price'=>'0.75'));
        $company->products->add($product);
        $this->assertEqual(2, $company->products->count());
        $company->save();
        $company_reloaded = new DummyDecorator(Company::$objects->get(1), 'test');
        $this->assertEqual(2, $company_reloaded->products->count());
    }
}

?>
