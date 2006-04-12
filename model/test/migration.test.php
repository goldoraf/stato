<?php

require_once(CORE_DIR.'/model/model.php');

class SimpleExceptionCatcherInvoker extends SimpleInvokerDecorator
{
    public function invoke($method)
    {
        try { parent::invoke($method); }
        catch (Exception $e)
        {
            $test_case = &$this->getTestCase();
            $test_case->exception($e);
        }
    }
}

class Person extends SActiveRecord
{

}

class MigrationTest extends UnitTestCase
{
    public function &createInvoker()
    {
        return new SimpleExceptionCatcherInvoker(new SimpleInvoker($this));
    }
    
    public function exception($e)
    {
        $this->_runner->paintFail(
                "Uncaught exception [{$e->getMessage()}] in [{$e->getFile()}] line [{$e->getLine()}]");
    }
    
    public function assertNothingThrown()
    {
        return $this->assertTrue(true);
    }
    
    public function tearDown()
    {
        try
        {
            Person::connection()->removeColumn('people', 'last_name');
            Person::connection()->removeColumn('people', 'bio');
            Person::connection()->removeColumn('people', 'age');
            Person::connection()->removeColumn('people', 'height');
            Person::connection()->removeColumn('people', 'birthday');
            Person::connection()->removeColumn('people', 'favorite_day');
            Person::connection()->removeColumn('people', 'male');
        }
        catch (Exception $e) { }
    }
    
    public function testCreateTable()
    {
        $t = new STable();
        $t->addPrimaryKey('id');
        $t->addColumn('first_name', 'string');
        Person::connection()->createTable('testing', $t);
        $this->assertNothingThrown();
        Person::connection()->dropTable('testing');
    }
    
    public function testNativeTypes()
    {
        SActiveStore::deleteAll('Person');
        Person::connection()->addColumn('people', 'last_name', 'string');
        Person::connection()->addColumn('people', 'bio', 'text');
        Person::connection()->addColumn('people', 'age', 'integer');
        Person::connection()->addColumn('people', 'height', 'float');
        Person::connection()->addColumn('people', 'birthday', 'datetime');
        Person::connection()->addColumn('people', 'favorite_day', 'date');
        Person::connection()->addColumn('people', 'male', 'boolean');
        SActiveStore::resetAttributeInformation('people');
        
        $p = new Person(array('first_name'=>'Neil', 'last_name'=>'Armstrong', 
        'bio'=>'First man on the moon...', 'age'=>76, 'height'=>1.72, /*'birthday'=>new SDateTime(1930,8,5),
        'favorite_day'=>new SDate(1969,07,21),*/ 'male'=>true));
        $p->birthday = new SDateTime(1930,8,5);
        $p->favorite_day = new SDate(1969,07,21);
        $p->save();
        $this->assertNothingThrown();
        
        $neil = SActiveStore::findFirst('Person');
        $this->assertEqual($neil->first_name, 'Neil');
        $this->assertEqual($neil->last_name, 'Armstrong');
        $this->assertEqual($neil->bio, 'First man on the moon...');
        $this->assertEqual($neil->age, 76);
        $this->assertEqual($neil->height, 1.72);
        $this->assertEqual($neil->birthday, new SDateTime(1930,8,5));
        $this->assertEqual($neil->favorite_day, new SDate(1969,07,21));
        $this->assertTrue($neil->male);
        
        $this->assertTrue(is_string($neil->first_name));
        $this->assertTrue(is_string($neil->bio));
        $this->assertTrue(is_int($neil->age));
        $this->assertTrue(is_float($neil->height));
    }
}

?>
