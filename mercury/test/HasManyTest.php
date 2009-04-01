<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

class HasManyTest extends ActiveTestCase
{
    public $fixtures = array('companies', 'products');
    public $models = array('dependent_company_1', 'dependent_company_2', 'dependent_company_3');
    
    public function test_add()
    {
        $company = Company::$objects->get(1);
        $product = new Product(array('name'=>'CD-R', 'price'=>'0.75'));
        $this->assertEquals(1, $company->products->count());
        $company->products->add($product);
        $this->assertEquals(2, $company->products->count());
    }
    
    public function test_add_sub_class()
    {
        $company = Company::$objects->get(1);
        $product = new SuperProduct(array('name'=>'CD-R', 'price'=>'0.75'));
        $this->assertEquals(1, $company->products->count());
        $company->products->add($product);
        $this->assertEquals(2, $company->products->count());
    }
    
    public function test_add_collection()
    {
        $nb_companies = Company::$objects->count();
        $nb_products = Product::$objects->count();
        $new_company = new Company(array('name'=>'OpenSource Inc.'));
        $product1 = new Product(array('name'=>'mouse', 'price'=>'14.95'));
        $product2 = new Product(array('name'=>'screen', 'price'=>'350.00'));
        $new_company->products->add(array($product2, $product1));
        $this->assertTrue($new_company->is_new_record());
        $this->assertTrue($product1->is_new_record());
        $new_company->save();
        $this->assertFalse($new_company->is_new_record());
        $this->assertFalse($product1->is_new_record());
        $this->assertEquals($nb_companies+1, Company::$objects->count());
        $this->assertEquals($nb_products+2, Product::$objects->count());
        $company_reloaded = Company::$objects->get($new_company->id);
        $this->assertEquals(2, $company_reloaded->products->count());
    }
    
    public function test_foreach()
    {
        $new_company = new Company(array('name'=>'MegaGeek corp.'));
        $new_company->products->add(new Product(array('name'=>'usb key', 'price'=>'34.95')));
        $new_company->products->add(new Product(array('name'=>'keyboard', 'price'=>'50.00')));
        $new_company->save();
        $i = 0;
        foreach($new_company->products->all() as $product)
        {
            $this->assertEquals('Product', get_class($product));
            $i++;
        }
        $this->assertEquals(2, $i);
    }
    
    public function test_create()
    {
        $company = Company::$objects->get(1);
        $nb_products = $company->products->count();
        $new_product = $company->products->create(array('name'=>'toaster', 'price'=>'15.00'));
        $this->assertEquals('toaster', $new_product->name);
        $this->assertFalse($new_product->is_new_record());
        $company_reloaded = Company::$objects->get(1);
        $this->assertEquals($nb_products + 1, $company_reloaded->products->count());
    }
    
    public function test_delete_dependency()
    {   
        $company = DependentCompany1::$objects->get(1);
        $company->delete();
        $this->assertEquals(0, Product::$objects->count());
    }
    
    public function test_delete_all_dependency()
    {
        $company = DependentCompany2::$objects->get(1);
        $company->delete();
        $this->assertEquals(0, Product::$objects->count());
    }
    
    public function test_nullify_dependency()
    {
        $company = DependentCompany3::$objects->get(1);
        $company->delete();
        $this->assertEquals(1, Product::$objects->count());
        $product = Product::$objects->get(1);
        $this->assertNull($product->company_id);
    }
}
