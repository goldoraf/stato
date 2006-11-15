<?php

class QuerySetTest extends ActiveTestCase
{
    public $fixtures = array('companies', 'employes');
    
    public function test_foreach()
    {
        $count = 0;
        foreach (Company::$objects->all() as $c)
        {
            $this->assertEqual('Company', get_class($c));
            $count++;
        }
        $this->assertEqual(2, $count);
    }
    
    public function test_get()
    {
        $emp = Employe::$objects->get(1);
        $this->assertEqual(1, $emp->id);
        $this->assertEqual('John', $emp->firstname);
        $this->assertEqual(Employe::$objects->get(1), Employe::$objects->get('1'));
        $emp = Employe::$objects->get("firstname = 'John'", "lastname = 'Doe'");
        $this->assertEqual(1, $emp->id);
        $this->assertEqual('John', $emp->firstname);
        $emps = Employe::$objects->get(array(1, 2));
        $this->assertTrue(is_array($emps));
        $this->assertEqual(1, $emps[1]->id);
        $this->assertEqual('John', $emps[1]->firstname);
        $this->assertEqual(2, $emps[2]->id);
        $this->assertEqual('Bridget', $emps[2]->firstname);
    }
    
    public function test_count()
    {
        $this->assertEqual(2, Employe::$objects->count());
        $this->assertEqual(1, Employe::$objects->filter("firstname = 'John'")->count());
    }
    
    public function test_values()
    {
        $companies = Company::$objects->filter("name = 'Groupe W'")->values();
        if ($companies->valid()) $c = $companies->current();
        $this->assertEqual(array('id' => 2, 'name' => 'Groupe W'), $c);
        $companies = Company::$objects->filter("name = 'Groupe W'")->values('id');
        if ($companies->valid()) $c = $companies->current();
        $this->assertEqual(2, $c);
    }
    
    public function test_filter()
    {
        $companies = Company::$objects->filter("name = 'Groupe W'");
        $this->assertEqual("WHERE name = 'Groupe W'", $companies->sql_clause());
        $this->assertEqual(1, $companies->count());
        
        $emp = Employe::$objects->filter("firstname = 'John'", "lastname = 'Doe'");
        $this->assertEqual("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEqual(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = 'John'")->filter("lastname = 'Doe'");
        $this->assertEqual("WHERE firstname = 'John' AND lastname = 'Doe'", $emp->sql_clause());
        $this->assertEqual(1, $emp->count());
    }
    
    public function test_exclude()
    {
        $companies = Company::$objects->exclude("name = 'Groupe W'");
        $this->assertEqual("WHERE NOT (name = 'Groupe W')", $companies->sql_clause());
        $this->assertEqual(1, $companies->count());
        
        $emp = Employe::$objects->exclude("firstname = 'John'", "lastname = 'Doe'");
        $this->assertEqual("WHERE NOT (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEqual(1, $emp->count());
        
        $emp = Employe::$objects->exclude("firstname = 'John'")->exclude("lastname = 'Doe'");
        $this->assertEqual("WHERE NOT (firstname = 'John') AND NOT (lastname = 'Doe')", $emp->sql_clause());
        $this->assertEqual(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = 'John'")->exclude("lastname = 'Doe'");
        $this->assertEqual("WHERE firstname = 'John' AND NOT (lastname = 'Doe')", $emp->sql_clause());
        $this->assertEqual(0, $emp->count());
    }
    
    public function test_filter_with_binded_param()
    {
        $companies = Company::$objects->filter("name = :company", array(':company' => 'Groupe W'));
        $this->assertEqual("WHERE name = 'Groupe W'", $companies->sql_clause());
        $this->assertEqual(1, $companies->count());
        
        $emp = Employe::$objects->filter("firstname = :first", "lastname = :last", array(':first' => 'John', ':last' => 'Doe'));
        $this->assertEqual("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEqual(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = :first", "lastname = :last", array('first' => 'John', 'last' => 'Doe'));
        $this->assertEqual("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEqual(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = ?", "lastname = ?", array('John', 'Doe'));
        $this->assertEqual("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEqual(1, $emp->count());
        
        $emp = Employe::$objects->filter("firstname = '%s'", "lastname = '%s'", array('John', 'Doe'));
        $this->assertEqual("WHERE (firstname = 'John' AND lastname = 'Doe')", $emp->sql_clause());
        $this->assertEqual(1, $emp->count());
    }
    
    public function test_limit()
    {
        $companies = Company::$objects->filter("name = 'Groupe W'")->limit(2);
        $this->assertEqual("WHERE name = 'Groupe W' LIMIT 2", $companies->sql_clause());
        $companies = Company::$objects->filter("name = 'Groupe W'")->limit(2, 5);
        $this->assertEqual("WHERE name = 'Groupe W' LIMIT 2 OFFSET 5", $companies->sql_clause());
    }
    
    public function test_order_by()
    {
        $companies = Company::$objects->filter("name = 'Groupe W'")->order_by('name');
        $this->assertEqual("WHERE name = 'Groupe W' ORDER BY name ASC", $companies->sql_clause());
        $companies = Company::$objects->filter("name = 'Groupe W'")->order_by('name', '-id');
        $this->assertEqual("WHERE name = 'Groupe W' ORDER BY name ASC, id DESC", $companies->sql_clause());
        $companies = Company::$objects->filter("name = 'Groupe W'")->order_by('companies.name', 'companies.-id');
        $this->assertEqual("WHERE name = 'Groupe W' ORDER BY companies.name ASC, companies.id DESC", $companies->sql_clause());
    }
    
    public function test_joins()
    {
        $companies = Company::$objects->filter("employes->lastname = 'Doe'");
        $this->assertEqual("LEFT OUTER JOIN employes ON employes.company_id = companies.id WHERE employes.lastname = 'Doe'", $companies->sql_clause());
        if ($companies->valid()) $c = $companies->current();
        $this->assertEqual('World Company', $c->name);
    }
    
    public function test_create()
    {
        $c = Company::$objects->create(array('name' => 'Stato Inc.'));
        $this->assertFalse($c->is_new_record());
        $this->assertEqual('Stato Inc.', $c->name);
        $c2 = Company::$objects->get($c->id);
        $this->assertEqual('Stato Inc.', $c2->name);
    }
    
    public function test_delete()
    {
        Employe::$objects->all()->delete();
        $this->assertEqual(0, Employe::$objects->all()->count());
        Company::$objects->filter("name = 'Groupe W'")->delete();
        $this->assertEqual(1, Company::$objects->all()->count());
    }
}

?>
