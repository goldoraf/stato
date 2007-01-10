<?php

require_once(STATO_CORE_PATH.'/model/model.php');

class Person extends SActiveRecord
{
    public static $objects;
}

class Reminder extends SActiveRecord
{
    public static $objects;
}

class MigrationTest extends StatoTestCase
{
    public function __construct()
    {
        parent::UnitTestCase();
        SMapper::add_manager_to_class('Person');
    }
    
    public function tearDown()
    {
        SActiveRecord::connection()->initialize_schema_information();
        SActiveRecord::connection()->update('UPDATE '.SMigrator::$schema_info_table_name
        .' SET version = 0');
        
        try { Reminder::connection()->drop_table('reminders'); }
        catch (Exception $e) { }
        try { Reminder::connection()->drop_table('testings'); }
        catch (Exception $e) { }
        
        try { Person::connection()->remove_column('people', 'last_name'); }
        catch (Exception $e) { }
        try { Person::connection()->remove_column('people', 'bio'); }
        catch (Exception $e) { }
        try { Person::connection()->remove_column('people', 'age'); }
        catch (Exception $e) { }
        try { Person::connection()->remove_column('people', 'height'); }
        catch (Exception $e) { }
        try { Person::connection()->remove_column('people', 'birthday'); }
        catch (Exception $e) { }
        try { Person::connection()->remove_column('people', 'favorite_day'); }
        catch (Exception $e) { }
        try { Person::connection()->remove_column('people', 'male'); }
        catch (Exception $e) { }
        try { Person::connection()->remove_column('people', 'administrator'); }
        catch (Exception $e) { }
        
        SMapper::reset_meta_information('Person');
    }
    
    public function test_create_table()
    {
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('first_name', 'string');
        SActiveRecord::connection()->create_table('testings', $t);
        $this->assertNothingThrown();
    }
    
    public function test_create_table_with_not_null_column()
    {
        $t = new STable();
        $t->add_column('foo', 'string', array('null'=>false));
        SActiveRecord::connection()->create_table('testings', $t);
        $this->assertNothingThrown();
        try { SActiveRecord::connection()->execute("insert into testings (foo) values (NULL)"); }
        catch (Exception $e) { }
        $this->assertEqual('SInvalidStatementException', get_class($e));
    }
    
    public function test_create_table_with_defaults()
    {
        $t = new STable();
        $t->add_column('one', 'string', array('default'=>'hello'));
        $t->add_column('two', 'boolean', array('default'=>true));
        $t->add_column('three', 'boolean', array('default'=>false));
        $t->add_column('four', 'integer', array('default'=>1));
        SActiveRecord::connection()->create_table('testings', $t);
        
        $columns = SActiveRecord::connection()->columns('testings');
        $this->assertEqual('hello', $columns['one']->default);
        $this->assertTrue($columns['two']->default);
        $this->assertFalse($columns['three']->default);
        $this->assertEqual(1, $columns['four']->default);
    }
    
    public function test_add_index()
    {
        Person::connection()->add_column('people', 'last_name', 'string');
        
        Person::connection()->add_index('people', 'last_name');
        Person::connection()->remove_index('people', 'last_name');
        $this->assertNothingThrown();
        Person::connection()->add_index('people', array('last_name', 'first_name'));
        Person::connection()->remove_index('people', 'last_name');
        $this->assertNothingThrown();
    }
    
    public function test_native_types()
    {
        Person::connection()->execute('DELETE FROM people');
        
        Person::connection()->add_column('people', 'last_name', 'string');
        Person::connection()->add_column('people', 'bio', 'text');
        Person::connection()->add_column('people', 'age', 'integer');
        Person::connection()->add_column('people', 'height', 'float');
        Person::connection()->add_column('people', 'birthday', 'datetime');
        Person::connection()->add_column('people', 'favorite_day', 'date');
        Person::connection()->add_column('people', 'male', 'boolean');
        
        SMapper::reset_meta_information('Person');
        
        $p = new Person(array('first_name'=>'Neil', 'last_name'=>'Armstrong', 
        'bio'=>'First man on the moon...', 'age'=>76, 'height'=>1.72, 'male'=>true));
        $p->birthday = new SDateTime(1930,8,5);
        $p->favorite_day = new SDate(1969,07,21);
        $p->save();
        $this->assertNothingThrown();
        
        $neil = Person::$objects->get(1);
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
    
    public function test_rename_column()
    {
        SActiveRecord::connection()->execute('DELETE FROM people');
        SActiveRecord::connection()->add_column('people', 'girlfriend', 'string');
        SMapper::reset_meta_information('Person');
        $p = new Person(array('girlfriend' => 'bobette'));
        $p->save();
        
        SActiveRecord::connection()->rename_column('people', 'girlfriend', 'exgirlfriend');
        SMapper::reset_meta_information('Person');
        $p = Person::$objects->get(2);
        $this->assertEqual('bobette', $p->exgirlfriend);
        
        try { SActiveRecord::connection()->remove_column('people', 'girlfriend'); }
        catch (Exception $e) { }
        try { SActiveRecord::connection()->remove_column('people', 'exgirlfriend'); }
        catch (Exception $e) { }
    }
    
    public function test_rename_table()
    {
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('url', 'string');
        SActiveRecord::connection()->create_table('bookmarks', $t);
        SActiveRecord::connection()->rename_table('bookmarks', 'favoris');
        $this->assertNothingThrown();
        SActiveRecord::connection()->execute("INSERT INTO favoris (url) VALUES ('http://www.rubyonrails.org')");
        $row = SActiveRecord::connection()->select_one('SELECT url FROM favoris WHERE id=1');
        $this->assertEqual('http://www.rubyonrails.org', $row['url']);
        
        try { SActiveRecord::connection()->drop_table('bookmarks'); }
        catch (Exception $e) { }
        try { SActiveRecord::connection()->drop_table('favoris'); }
        catch (Exception $e) { }
    }
    
    public function test_change_column()
    {
        SActiveRecord::connection()->add_column('people', 'age', 'integer');
        $old_columns = SActiveRecord::connection()->columns('people');
        $this->assertEqual('integer', $old_columns['age']->type);
        
        SActiveRecord::connection()->change_column('people', 'age', 'string');
        $this->assertNothingThrown();
        $new_columns = SActiveRecord::connection()->columns('people');
        $this->assertEqual('string', $new_columns['age']->type);
    }
    
    public function test_change_column_with_new_default()
    {
        SActiveRecord::connection()->add_column('people', 'administrator', 'boolean', array('default'=>true));
        SMapper::reset_meta_information('Person');
        $p = new Person();
        $this->assertTrue($p->administrator);
        
        SActiveRecord::connection()->change_column('people', 'administrator', 'boolean', array('default'=>false));
        $this->assertNothingThrown();
        SMapper::reset_meta_information('Person');
        $p = new Person();
        $this->assertFalse($p->administrator);
    }
    
    public function test_migrator()
    {
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(in_array('reminders', SActiveRecord::connection()->tables()));
        
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate');
        
        $this->assertEqual(2, SMigrator::current_version());
        SMapper::reset_meta_information('Person');
        SMapper::add_manager_to_class('Reminder');
        $this->assertTrue(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $r = new Reminder(array('content'=>'hello world', 'remind_at'=>SDateTime::today()));
        $r->save();
        $this->assertEqual('hello world', Reminder::$objects->get(1)->content);
        
        SMigrator::down(dirname(__FILE__).'/fixtures/migrate');
        
        $this->assertEqual(0, SMigrator::current_version());
        SMapper::reset_meta_information('Person');
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(in_array('reminders', SActiveRecord::connection()->tables()));
    }
    
    public function test_migrator_one_up()
    {
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(in_array('reminders', SActiveRecord::connection()->tables()));
        
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate', 1);
        
        SMapper::reset_meta_information('Person');
        $this->assertTrue(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(in_array('reminders', SActiveRecord::connection()->tables()));
        
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate', 2);
        SMapper::add_manager_to_class('Reminder');
        $r = new Reminder(array('content'=>'hello world', 'remind_at'=>SDateTime::today()));
        $r->save();
        $this->assertEqual('hello world', Reminder::$objects->get(1)->content);
    }
    
    public function test_migrator_one_down()
    {
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate');
        SMigrator::down(dirname(__FILE__).'/fixtures/migrate', 1);
        SMapper::reset_meta_information('Person');
        $this->assertTrue(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(in_array('reminders', SActiveRecord::connection()->tables()));
    }
    
    public function test_migrator_one_up_one_down()
    {
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate', 1);
        SMigrator::down(dirname(__FILE__).'/fixtures/migrate', 0);
        
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(in_array('reminders', SActiveRecord::connection()->tables()));
    }
    
    public function test_migrator_going_down_due_to_version_target()
    {
        SMigrator::up(dirname(__FILE__).'/fixtures/migrate', 1);
        SMigrator::migrate(dirname(__FILE__).'/fixtures/migrate', 0);
        
        $this->assertFalse(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $this->assertFalse(in_array('reminders', SActiveRecord::connection()->tables()));
        
        SMigrator::migrate(dirname(__FILE__).'/fixtures/migrate');
        
        $this->assertEqual(2, SMigrator::current_version());
        SMapper::reset_meta_information('Person');
        SMapper::add_manager_to_class('Reminder');
        $this->assertTrue(in_array('last_name', array_keys(SActiveRecord::connection()->columns('people'))));
        $r = new Reminder(array('content'=>'hello world', 'remind_at'=>SDateTime::today()));
        $r->save();
        $this->assertEqual('hello world', Reminder::$objects->get(1)->content);
    }
}

?>
