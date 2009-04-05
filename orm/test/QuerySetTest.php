<?php

require_once dirname(__FILE__) . '/../../test/TestsHelper.php';

class QuerySetTest extends ActiveTestCase
{
    public $fixtures = array('companies', 'employes');
    
    public function test_foreach()
    {
        $count = 0;
        foreach (Company::$objects->all() as $c)
        {
            $this->assertEquals('Company', get_class($c));
            $count++;
        }
        $this->assertEquals(2, $count);
    }
    
    public function test_get()
    {
        $emp = Employe::$objects->get(1);
        $this->assertEquals(1, $emp->id);
        $this->assertEquals('John', $emp->firstname);
        $this->assertEquals(Employe::$objects->get(1), Employe::$objects->get('1'));
        $emp = Employe::$objects->get("firstname = 'John'", "lastname = 'Doe'");
        $this->assertEquals(1, $emp->id);
        $this->assertEquals('John', $emp->firstname);
    }
    
    public function test_assertion_error()
    {
        Employe::$objects->create(array('firstname' => 'John', 'lastname' => 'Ryan'));
        $this->setExpectedException('SAssertionError');
        $emp = Employe::$objects->get("firstname = 'John'");
    }
    
    public function test_record_not_found()
    {
        $this->setExpectedException('SRecordNotFound');
        $emp = Employe::$objects->get(999);
    }
    
    public function test_get_or_create()
    {
        $this->assertEquals(2, Employe::$objects->count());
        $emp = Employe::$objects->get_or_create(array('firstname' => 'John', 'lastname' => 'Doe'));
        $this->assertEquals(1, $emp->id);
        $this->assertEquals('Doe', $emp->lastname);
        $this->assertEquals(2, Employe::$objects->count());
        $emp = Employe::$objects->get_or_create(array('firstname' => 'John', 'lastname' => 'Ryan'));
        $this->assertEquals('Ryan', $emp->lastname);
        $this->assertEquals(3, Employe::$objects->count());
    }
    
    public function test_in_bulk()
    {
        $emps = Employe::$objects->in_bulk(array(1, 2));
        $this->assertTrue(is_array($emps));
        $this->assertEquals(2, count($emps));
        $this->assertEquals(1, $emps[1]->id);
        $this->assertEquals('John', $emps[1]->firstname);
        $this->assertEquals(2, $emps[2]->id);
        $this->assertEquals('Bridget', $emps[2]->firstname);
        $emps = Employe::$objects->in_bulk(array(2));
        $this->assertTrue(is_array($emps));
        $this->assertEquals(1, count($emps));
        $this->assertEquals(2, $emps[2]->id);
        $this->assertEquals('Bridget', $emps[2]->firstname);
        $emps = Employe::$objects->in_bulk(array(999));
        $this->assertTrue(is_array($emps));
        $this->assertEquals(0, count($emps));
        $emps = Employe::$objects->in_bulk(array());
        $this->assertTrue(is_array($emps));
        $this->assertEquals(0, count($emps));
    }
    
    public function test_count()
    {
        $this->assertEquals(2, Employe::$objects->count());
        $this->assertEquals(1, Employe::$objects->filter("firstname = 'John'")->count());
    }
    
    public function test_values()
    {
        $companies = Company::$objects->filter("name = 'Groupe W'")->values();
        if ($companies->valid()) $c = $companies->current();
        $this->assertEquals(array('id' => 2, 'name' => 'Groupe W'), $c);
        $companies = Company::$objects->filter("name = 'Groupe W'")->values('id');
        if ($companies->valid()) $c = $companies->current();
        $this->assertEquals(2, $c);
    }
    
    public function test_filter()
    {
        $companies = Company::$objects->filter("name = 'Groupe W'");
        $this->assertEquals("WHERE name = 'Groupe W'", $companies->sql_clause());
        $this->assertEquals(1, $companies->count());
        
        $emp = Employe::$objects->filter("firstname = 'John'", "lastname = 'Doe'");
        $this->assertEquals("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEquals(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = 'John'")->filter("lastname = 'Doe'");
        $this->assertEquals("WHERE firstname = 'John' AND lastname = 'Doe'", $emp->sql_clause());
        $this->assertEquals(1, $emp->count());
    }
    
    public function test_exclude()
    {
        $companies = Company::$objects->exclude("name = 'Groupe W'");
        $this->assertEquals("WHERE NOT (name = 'Groupe W')", $companies->sql_clause());
        $this->assertEquals(1, $companies->count());
        
        $emp = Employe::$objects->exclude("firstname = 'John'", "lastname = 'Doe'");
        $this->assertEquals("WHERE NOT (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEquals(1, $emp->count());
        
        $emp = Employe::$objects->exclude("firstname = 'John'")->exclude("lastname = 'Doe'");
        $this->assertEquals("WHERE NOT (firstname = 'John') AND NOT (lastname = 'Doe')", $emp->sql_clause());
        $this->assertEquals(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = 'John'")->exclude("lastname = 'Doe'");
        $this->assertEquals("WHERE firstname = 'John' AND NOT (lastname = 'Doe')", $emp->sql_clause());
        $this->assertEquals(0, $emp->count());
    }
    
    public function test_filter_with_binded_param()
    {
        $companies = Company::$objects->filter("name = :company", array(':company' => 'Groupe W'));
        $this->assertEquals("WHERE name = 'Groupe W'", $companies->sql_clause());
        $this->assertEquals(1, $companies->count());
        
        $emp = Employe::$objects->filter("firstname = :first", "lastname = :last", array(':first' => 'John', ':last' => 'Doe'));
        $this->assertEquals("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEquals(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = :first", "lastname = :last", array('first' => 'John', 'last' => 'Doe'));
        $this->assertEquals("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEquals(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = ?", "lastname = ?", array('John', 'Doe'));
        $this->assertEquals("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEquals(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = '%s'", "lastname = '%s'", array('John', 'Doe'));
        $this->assertEquals("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEquals(1, $emp->count());
    }
    
    public function test_limit()
    {
        $companies = Company::$objects->filter("name = 'Groupe W'")->limit(2);
        $this->assertEquals("WHERE name = 'Groupe W' LIMIT 2", $companies->sql_clause());
        $companies = Company::$objects->filter("name = 'Groupe W'")->limit(2, 5);
        $this->assertEquals("WHERE name = 'Groupe W' LIMIT 2 OFFSET 5", $companies->sql_clause());
    }
    
    public function test_order_by()
    {
        $companies = Company::$objects->filter("name = 'Groupe W'")->order_by('name');
        $this->assertEquals("WHERE name = 'Groupe W' ORDER BY name ASC", $companies->sql_clause());
        $companies = Company::$objects->filter("name = 'Groupe W'")->order_by('name', '-id');
        $this->assertEquals("WHERE name = 'Groupe W' ORDER BY name ASC, id DESC", $companies->sql_clause());
        $companies = Company::$objects->filter("name = 'Groupe W'")->order_by('companies.name', 'companies.-id');
        $this->assertEquals("WHERE name = 'Groupe W' ORDER BY companies.name ASC, companies.id DESC", $companies->sql_clause());
    }
    
    public function test_joins()
    {
        $companies = Company::$objects->filter("employes->lastname = 'Doe'");
        $this->assertEquals("LEFT OUTER JOIN employes ON employes.company_id = companies.id WHERE employes.lastname = 'Doe'", $companies->sql_clause());
        $this->assertEquals('World Company', $companies->first()->name);
    }
    
    public function test_custom_sql()
    {
        $companies = Company::$objects->by_sql("SELECT companies.* FROM companies LEFT OUTER JOIN employes ON employes.company_id = companies.id WHERE employes.lastname = 'Doe'");
        $this->assertEquals('World Company', $companies->first()->name);
    }
    
    public function test_create()
    {
        $c = Company::$objects->create(array('name' => 'Stato Inc.'));
        $this->assertFalse($c->is_new_record());
        $this->assertEquals('Stato Inc.', $c->name);
        $c2 = Company::$objects->get($c->id);
        $this->assertEquals('Stato Inc.', $c2->name);
    }
    
    public function test_delete()
    {
        Employe::$objects->all()->delete();
        $this->assertEquals(0, Employe::$objects->all()->count());
        Company::$objects->filter("name = 'Groupe W'")->delete();
        $this->assertEquals(1, Company::$objects->all()->count());
    }
}

