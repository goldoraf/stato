<?php

class DummyDecorator extends SActiveRecordDecorator
{
    protected $testArg = null;
    
    public function __construct($record, $testArg)
    {
        $this->record = $record;
        $this->testArg = $testArg;
    }
    
    public function testMethod()
    {
        return 1;
    }
}

class DecoratorsTest extends ActiveTestCase
{
    public $fixtures = array('posts', 'companies', 'products');
    
    function testBasics()
    {
        $post = new DummyDecorator(SActiveStore::findByPk('Post', 1), 'test');
        $this->assertEqual('Frameworks : A new hope...', $post->title);
        $this->assertEqual(1, $post->testMethod());
        $post->title = 'Framework clone wars';
        $this->assertEqual('Framework clone wars', $post->title);
        $this->assertEqual(array('title', 'author', 'text', 'published'), $post->contentAttributes());
        $post->save();
        $postBis = new DummyDecorator(SActiveStore::findByPk('Post', 1), 'test');
        $this->assertEqual($post->title, $postBis->title);
        $this->assertEqual($post->author, $postBis->author);
    }
    
    function testAssociations()
    {
        $company = new DummyDecorator(SActiveStore::findByPk('Company', 1), 'test');
        $this->assertEqual(1, $company->products->count());
        $this->assertEqual(1, $company->countProducts());
        $product = new Product(array('name'=>'CD-R', 'price'=>'0.75'));
        $company->products[] = $product;
        $this->assertEqual(2, $company->products->count());
        $this->assertEqual(2, $company->countProducts());
        $company->save();
        $companyReloaded = new DummyDecorator(SActiveStore::findByPk('Company', 1), 'test');
        $this->assertEqual(2, $companyReloaded->products->count());
        $this->assertEqual(2, $companyReloaded->countProducts());
    }
}

?>
