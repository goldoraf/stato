<?php

require_once(CORE_DIR.'/db/db.php');

class CompanyModel extends EntityModel
{
    public $tableName = 'companies';
}

class ProductModel extends EntityModel
{
    public $tableName = 'products';
}

class StaticFinderTest extends UnitTestCase
{
    function testManager()
    {
        EntityManager::load('company');
        EntityManager::load('product');
        $this->assertTrue(class_exists('CompanyFinder'));
        $set = CompanyFinder::findAll();
        $this->assertEqual(2, count($set));
        $set = CompanyFinder::findFirst();
        $this->assertEqual(1, count($set));
    }
}
?>
