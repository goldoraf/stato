<?php

class FakeRecord extends SObservable
{
    public $value = 0;
    public $beforeValue = Null;
    public $afterValue = Null;
    public $association = Null;
    
    public function __construct()
    {
        $this->association = new FakeAssoc();
        $this->addCallback($this->association, 'beforeSave', 'increment');
    }
    
    public function save()
    {
        $this->setState('beforeSave');
        $this->value++;
        $this->setState('afterSave');
    }
    
    public function beforeSave()
    {
        $this->beforeValue = $this->value;
    }
    
    public function afterSave()
    {
        $this->afterValue = $this->value;
    }
}

class FakeAssoc
{
    public $value = 0;
    
    public function increment()
    {
        $this->value++;
    }
}

class CallbacksTest extends UnitTestCase
{
    function testSelfCallbacks()
    {
        $entity = new FakeRecord();
        $this->assertEqual(0, $entity->value);
        $entity->save();
        $this->assertEqual(0, $entity->beforeValue);
        $this->assertEqual(1, $entity->value);
        $this->assertEqual(1, $entity->afterValue);
    }
    
    function testCallbacks()
    {
        $entity = new FakeRecord();
        $this->assertEqual(0, $entity->association->value);
        $entity->save();
        $this->assertEqual(1, $entity->association->value);
    }
}

?>
