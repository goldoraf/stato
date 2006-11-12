<?php

class FakeRecord extends SObservable
{
    public $value = 0;
    public $before_value = Null;
    public $after_value = Null;
    public $association = Null;
    
    public function __construct()
    {
        $this->association = new FakeAssoc();
        $this->add_callback($this->association, 'before_save', 'increment');
    }
    
    public function save()
    {
        $this->set_state('before_save');
        $this->value++;
        $this->set_state('after_save');
    }
    
    public function before_save()
    {
        $this->before_value = $this->value;
    }
    
    public function after_save()
    {
        $this->after_value = $this->value;
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
    function test_self_callbacks()
    {
        $entity = new FakeRecord();
        $this->assertEqual(0, $entity->value);
        $entity->save();
        $this->assertEqual(0, $entity->before_value);
        $this->assertEqual(1, $entity->value);
        $this->assertEqual(1, $entity->after_value);
    }
    
    function test_callbacks()
    {
        $entity = new FakeRecord();
        $this->assertEqual(0, $entity->association->value);
        $entity->save();
        $this->assertEqual(1, $entity->association->value);
    }
}

?>
