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

class Person extends SActiveRecord {}
class Reminder extends SActiveRecord {}

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
        SActiveRecord::connection()->initializeSchemaInformation();
        SActiveRecord::connection()->update('UPDATE '.SMigrator::$schemaInfoTableName
        .' SET version = 0');
        
        try { Reminder::connection()->dropTable('reminders'); }
        catch (Exception $e) { }
        try { Reminder::connection()->dropTable('testings'); }
        catch (Exception $e) { }
        
        try { Person::connection()->removeColumn('people', 'last_name'); }
        catch (Exception $e) { }
        try { Person::connection()->removeColumn('people', 'bio'); }
        catch (Exception $e) { }
        try { Person::connection()->removeColumn('people', 'age'); }
        catch (Exception $e) { }
        try { Person::connection()->removeColumn('people', 'height'); }
        catch (Exception $e) { }
        try { Person::connection()->removeColumn('people', 'birthday'); }
        catch (Exception $e) { }
        try { Person::connection()->removeColumn('people', 'favorite_day'); }
        catch (Exception $e) { }
        try { Person::connection()->removeColumn('people', 'male'); }
        catch (Exception $e) { }
        try { Person::connection()->removeColumn('people', 'administrator'); }
        catch (Exception $e) { }
        
        SActiveStore::resetAttributeInformation('people');
    }
    
    public function testCreateTable()
    {
        $t = new STable();
        $t->addPrimaryKey('id');
        $t->addColumn('first_name', 'string');
        SActiveRecord::connection()->createTable('testings', $t);
        $this->assertNothingThrown();
    }
    
    public function testCreateTableAddsId()
    {
        $t = new STable();
        $t->addColumn('foo', 'string');
        SActiveRecord::connection()->createTable('testings', $t);
        $this->assertNothingThrown();
        $this->assertTrue(in_array('id', array_keys(SActiveRecord::connection()->columns('testings'))));
    }
    
    public function testCreateTableWithNotNullColumn()
    {
        $t = new STable();
        $t->addColumn('foo', 'string', array('null'=>false));
        SActiveRecord::connection()->createTable('testings', $t);
        $this->assertNothingThrown();
        try { SActiveRecord::connection()->execute("insert into testings (foo) values (NULL)"); }
        catch (Exception $e) { }
        $this->assertEqual('SInvalidStatementException', get_class($e));
    }
    
    public function testCreateTableWithDefaults()
    {
        $t = new STable();
        $t->addColumn('one', 'string', array('default'=>'hello'));
        $t->addColumn('two', 'boolean', array('default'=>true));
        $t->addColumn('three', 'boolean', array('default'=>false));
        $t->addColumn('four', 'integer', array('default'=>1));
        SActiveRecord::connection()->createTable('testings', $t);
        
        $columns = SActiveRecord::connection()->columns('testings');
        $this->assertEqual('hello', $columns['one']->default);
        $this->assertTrue($columns['two']->default);
        $this->assertFalse($columns['three']->default);
        $this->assertEqual(1, $columns['four']->default);
    }
    
    public function testAddIndex()
    {
        Person::connection()->addColumn('people', 'last_name', 'string');
        
        Person::connection()->addIndex('people', 'last_name');
        Person::connection()->removeIndex('people', 'last_name');
        $this->assertNothingThrown();
        Person::connection()->addIndex('people', array('last_name', 'first_name'));
        Person::connection()->removeIndex('people', 'last_name');
        $this->assertNothingThrown();
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
    
    public function testRenameColumn()
    {
        SActiveStore::deleteAll('person');
        SActiveRecord::connection()->addColumn('people', 'girlfriend', 'string');
        SActiveStore::resetAttributeInformation('people');
        $p = new Person(array('girlfriend' => 'bobette'));
        $p->save();
        
        SActiveRecord::connection()->renameColumn('people', 'girlfriend', 'exgirlfriend');
        SActiveStore::resetAttributeInformation('people');
        $p = SActiveStore::findFirst('person');
        $this->assertEqual('bobette', $p->exgirlfriend);
        
        try { SActiveRecord::connection()->removeColumn('people', 'girlfriend'); }
        catch (Exception $e) { }
        try { SActiveRecord::connection()->removeColumn('people', 'exgirlfriend'); }
        catch (Exception $e) { }
    }
    
    public function testRenameTable()
    {
        $t = new STable();
        $t->addColumn('url', 'string');
        SActiveRecord::connection()->createTable('bookmarks', $t);
        SActiveRecord::connection()->renameTable('bookmarks', 'favoris');
        $this->assertNothingThrown();
        SActiveRecord::connection()->execute("INSERT INTO favoris (url) VALUES ('http://www.rubyonrails.org')");
        $row = SActiveRecord::connection()->selectOne('SELECT url FROM favoris WHERE id=1');
        $this->assertEqual('http://www.rubyonrails.org', $row['url']);
        
        try { SActiveRecord::connection()->dropTable('bookmarks'); }
        catch (Exception $e) { }
        try { SActiveRecord::connection()->dropTable('favoris'); }
        catch (Exception $e) { }
    }
    
    public function testChangeColumn()
    {
        SActiveRecord::connection()->addColumn('people', 'age', 'integer');
        $oldColumns = SActiveRecord::connection()->columns('people');
        $this->assertEqual('integer', $oldColumns['age']->type);
        
        SActiveRecord::connection()->changeColumn('people', 'age', 'string');
        $this->assertNothingThrown();
        $newColumns = SActiveRecord::connection()->columns('people');
        $this->assertEqual('string', $newColumns['age']->type);
    }
    
    public function testChangeColumnWithNewDefault()
    {
        SActiveRecord::connection()->addColumn('people', 'administrator', 'boolean', array('default'=>true));
        SActiveStore::resetAttributeInformation('people');
        $p = new Person();
        $this->assertTrue($p->administrator);
        
        SActiveRecord::connection()->changeColumn('people', 'administrator', 'boolean', array('default'=>false));
        $this->assertNothingThrown();
        SActiveStore::resetAttributeInformation('people');
        $p = new Person();
        $this->assertFalse($p->administrator);
    }
    
    public function testMigrator()
    {
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(SActiveStore::tableExists('reminders'));
        
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate');
        
        $this->assertEqual(2, SMigrator::currentVersion());
        SActiveStore::resetAttributeInformation('people');
        $this->assertTrue(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $r = new Reminder(array('content'=>'hello world', 'remind_at'=>SDateTime::today()));
        $r->save();
        $this->assertEqual('hello world', SActiveStore::findFirst('reminder')->content);
        
        SMigrator::down(dirname(__FILE__).'/fixtures/migrate');
        
        $this->assertEqual(0, SMigrator::currentVersion());
        SActiveStore::resetAttributeInformation('people');
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(SActiveStore::tableExists('reminders'));
    }
    
    public function testMigratorOneUp()
    {
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(SActiveStore::tableExists('reminders'));
        
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate', 1);
        
        SActiveStore::resetAttributeInformation('people');
        $this->assertTrue(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(SActiveStore::tableExists('reminders'));
        
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate', 2);
        
        $r = new Reminder(array('content'=>'hello world', 'remind_at'=>SDateTime::today()));
        $r->save();
        $this->assertEqual('hello world', SActiveStore::findFirst('reminder')->content);
    }
    
    public function testMigratorOneDown()
    {
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate');
        SMigrator::down(dirname(__FILE__).'/fixtures/migrate', 1);
        SActiveStore::resetAttributeInformation('people');
        $this->assertTrue(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(SActiveStore::tableExists('reminders'));
    }
    
    public function testMigratorOneUpOneDown()
    {
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate', 1);
        SMigrator::down(dirname(__FILE__).'/fixtures/migrate', 0);
        
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(SActiveStore::tableExists('reminders'));
    }
    
    public function testMigratorGoingDownDueToVersionTarget()
    {
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate', 1);
        SMigrator::migrate(dirname(__FILE__).'/fixtures/migrate', 0);
        
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(SActiveStore::tableExists('reminders'));
        
        SMigrator::migrate(dirname(__FILE__).'/fixtures/migrate');
        
        $this->assertEqual(2, SMigrator::currentVersion());
        SActiveStore::resetAttributeInformation('people');
        $this->assertTrue(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $r = new Reminder(array('content'=>'hello world', 'remind_at'=>SDateTime::today()));
        $r->save();
        $this->assertEqual('hello world', SActiveStore::findFirst('reminder')->content);
    }
}

?>
