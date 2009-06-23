<?php

namespace Stato\Orm;

require_once __DIR__ . '/../TestsHelper.php';

class Product {}
class Company {}

class RelationsTest extends TestCase
{
    protected $fixtures = array('companies', 'products');
    
    public function setup()
    {
        parent::setup();
        $this->companies = new Table('companies', array(
            new Column('id', Column::INTEGER, array('primary_key' => true)),
            new Column('name', Column::STRING),
        ));
        $this->products = new Table('products', array(
            new Column('id', Column::INTEGER, array('primary_key' => true)),
            new Column('name', Column::STRING),
            new Column('price', Column::FLOAT),
            new Column('company_id', Column::INTEGER),
        ));
    }
    
    public function testBasic()
    {
        //$p = new Product();
    }
}