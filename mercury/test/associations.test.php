<?php

class SBelongsToTest extends ActiveTestCase
{
    public $fixtures = array('profiles', 'employes');
    public $models = array('dependent_company_1', 'dependent_company_2', 'dependent_company_3');
    
    public function test_belongs_to()
    {
        $profile = Profile::$objects->get(1);
        $employe = Employe::$objects->get(1);
        $this->assertEqual($profile->employe->lastname, $employe->lastname);
    }
    
    public function test_assignment()
    {
        $profile = new Profile(array('cv'=>'GNU expert'));
        $employe = new Employe(array('lastname'=>'Richard', 'firstname'=>'Stallman'));
        $this->assertTrue($profile->employe->is_null());
        $profile->employe = $employe;
        $this->assertFalse($profile->employe->is_null());
        $profile->save();
        $this->assertEqual($employe->id, $profile->employe_id);
    }
    
    public function test_null_assignment()
    {
        $profile = Profile::$objects->get(1);
        $this->assertFalse($profile->employe->is_null());
        $profile->employe = null;
        $this->assertTrue($profile->employe->is_null());
        $profile->save();
        $profile2 = Profile::$objects->get(1);
        $this->assertTrue($profile2->employe->is_null());
    }
    
    public function test_assignment_before_parent_saved()
    {
        $profile = Profile::$objects->get(1);
        $employe = new Employe(array('lastname'=>'Max', 'firstname'=>'Payne'));
        $profile->employe = $employe;
        $this->assertEqual($profile->employe->lastname, $employe->lastname);
        $this->assertTrue($employe->is_new_record());
        $profile->save();
        $this->assertFalse($employe->is_new_record());
        $this->assertEqual($employe->id, $profile->employe_id);
    }
    
    public function test_assignment_before_child_saved()
    {
        $employe = Employe::$objects->get(1);
        $profile = new Profile(array('cv'=>'Mozilla expert'));
        $profile->employe = $employe;
        $this->assertTrue($profile->is_new_record());
        $profile->save();
        $this->assertFalse($profile->is_new_record());
        $this->assertFalse($employe->is_new_record());
        $this->assertEqual($employe->id, $profile->employe_id);
    }
    
    public function test_assignment_before_either_saved()
    {
        $employe = new Employe(array('lastname'=>'Max', 'firstname'=>'Payne'));
        $profile = new Profile(array('cv'=>'Lone private'));
        $profile->employe = $employe;
        $this->assertTrue($profile->is_new_record());
        $this->assertTrue($employe->is_new_record());
        $profile->save();
        $this->assertFalse($profile->is_new_record());
        $this->assertFalse($employe->is_new_record());
        $this->assertEqual($employe->id, $profile->employe_id);
    }
}

class SHasManyTest extends ActiveTestCase
{
    public $fixtures = array('companies', 'products');
    
    public function test_add()
    {
        $company = Company::$objects->get(1);
        $product = new Product(array('name'=>'CD-R', 'price'=>'0.75'));
        $this->assertEqual(1, $company->products->count());
        $company->products->add($product);
        $this->assertEqual(2, $company->products->count());
    }
    
    public function test_add_sub_class()
    {
        $company = Company::$objects->get(1);
        $product = new SuperProduct(array('name'=>'CD-R', 'price'=>'0.75'));
        $this->assertEqual(1, $company->products->count());
        $company->products->add($product);
        $this->assertEqual(2, $company->products->count());
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
        $this->assertEqual($nb_companies+1, Company::$objects->count());
        $this->assertEqual($nb_products+2, Product::$objects->count());
        $company_reloaded = Company::$objects->get($new_company->id);
        $this->assertEqual(2, $company_reloaded->products->count());
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
            $this->assertEqual('Product', get_class($product));
            $i++;
        }
        $this->assertEqual(2, $i);
    }
    
    public function test_create()
    {
        $company = Company::$objects->get(1);
        $nb_products = $company->products->count();
        $new_product = $company->products->create(array('name'=>'toaster', 'price'=>'15.00'));
        $this->assertEqual('toaster', $new_product->name);
        $this->assertFalse($new_product->is_new_record());
        $company_reloaded = Company::$objects->get(1);
        $this->assertEqual($nb_products + 1, $company_reloaded->products->count());
    }
    
    public function test_delete_dependency()
    {
        $company = DependentCompany1::$objects->get(1);
        $company->delete();
        $this->assertEqual(0, Product::$objects->count());
    }
    
    public function test_delete_all_dependency()
    {
        $company = DependentCompany2::$objects->get(1);
        $company->delete();
        $this->assertEqual(0, Product::$objects->count());
    }
    
    public function test_nullify_dependency()
    {
        $company = DependentCompany3::$objects->get(1);
        $company->delete();
        $this->assertEqual(1, Product::$objects->count());
        $product = Product::$objects->get(1);
        $this->assertNull($product->company_id);
    }
}

class SHasManyThroughTest extends ActiveTestCase
{
    public $fixtures = array('companies', 'employes', 'profiles');
    
    public function test_has_many_join_model()
    {
        $comp = Company::$objects->get(1);
        $profiles = $comp->profiles->all()->to_array();
        $this->assertEqual(2, count($profiles));
        $this->assertEqual('blablabla', $profiles[0]->cv);
        $this->assertEqual('xxx', $profiles[1]->cv);
    }
    
    public function test_belongs_to_join_model()
    {
    
    }
}

class SManyToManyTest extends ActiveTestCase
{
    public $fixtures = array('developers', 'projects', 'developers_projects');
    
    public function test_basic()
    {
        $ben = Developer::$objects->get(1);
        $this->assertEqual(2, $ben->projects->count());
        $proj = Project::$objects->get(1);
        $this->assertEqual(1, $proj->developers->count());
    }
    
    public function test_add()
    {
        $richard = Developer::$objects->get(2);
        $proj = Project::$objects->get(1);
        $this->assertEqual(1, $richard->projects->count());
        $this->assertEqual(1, $proj->developers->count());
        $richard->projects->add($proj);
        $this->assertEqual(2, $richard->projects->count());
        $this->assertEqual(2, $proj->developers->count());
    }
    
    public function test_add_collection()
    {
    
    }
    
    public function test_add_before_save()
    {
        $nb_devels = Developer::$objects->count();
        $nb_projs  = Project::$objects->count();
        $peter = new Developer(array('name' => 'peter'));
        $proj1 = new Project(array('name' => 'WebNuked2.0'));
        $proj2 = new Project(array('name' => 'TotalWebInnov'));
        $peter->projects->add($proj1);
        $peter->projects->add($proj2);
        $this->assertTrue($peter->is_new_record());
        $this->assertTrue($proj1->is_new_record());
        $this->assertEqual(2, $peter->projects->count());
        $this->assertEqual($nb_projs, Project::$objects->count());
        $peter->save();
        $this->assertFalse($peter->is_new_record());
        $this->assertFalse($proj1->is_new_record());
        $this->assertEqual($nb_devels+1, Developer::$objects->count());
        $this->assertEqual($nb_projs+2, Project::$objects->count());
        $this->assertEqual(2, $peter->projects->count());
        $peter2 = Developer::$objects->get($peter->id);
        $this->assertEqual(2, $peter2->projects->count());
    }
    
    public function test_create()
    {
        $richard = Developer::$objects->get(2);
        $proj = $richard->projects->create(array('name' => 'PlzNotAnotherRecursiveAcronym'));
        $projects = $richard->projects->all()->to_array();
        $this->assertEqual($projects[1]->name, $proj->name);
        $this->assertFalse($proj->is_new_record());
    }
    
    public function test_delete()
    {
        $ben = Developer::$objects->get(1);
        $proj = Project::$objects->get(1);
        $this->assertEqual(2, $ben->projects->count());
        $this->assertEqual(1, $proj->developers->count());
        $ben->projects->delete($proj);
        $this->assertEqual(1, $ben->projects->count());
        $ben2 = Developer::$objects->get(1);
        $this->assertEqual(1, $ben2->projects->count());
        $proj2 = Project::$objects->get(1);
        $this->assertEqual(0, $proj2->developers->count());
    }
    
    public function test_delete_collection()
    {
    
    }
    
    public function test_clear()
    {
        $richard = Developer::$objects->get(2);
        $richard->projects->clear();
        $this->assertEqual(0, $richard->projects->count());
    }
}

class SHasOneTest extends ActiveTestCase
{
    public $fixtures = array('clients', 'contracts', 'projects');
    
    public function test_basic()
    {
        $client = Client::$objects->get(1);
        $contract = Contract::$objects->get(1);
        $this->assertCopy($contract, $client->contract->target());
        $this->assertEqual($contract->code, $client->contract->code);
    }
    
    public function test_type_mismatch()
    {
        $client = Client::$objects->get(1);
        try
        {
            $client->contract = Project::$objects->get(1);
        }
        catch (Exception $e)
        {
            $this->assertEqual('SAssociationTypeMismatch', get_class($e));
        }
    }
    
    public function test_natural_assignment()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = new Contract(array('code' => 'test'));
        $contract->save();
        $client->contract = $contract;
        $this->assertEqual($client->id, $contract->client_id);
    }
    
    public function test_assignment_to_null()
    {
        $client = Client::$objects->get(1);
        $this->assertFalse($client->contract->is_null());
        $client->contract = null;
        $client->save();
        $this->assertTrue($client->contract->is_null());
        $client2 = Client::$objects->get(1);
        $this->assertTrue($client2->contract->is_null());
        // il faudrait rendre la classe Contract 'dependent' de la classe Client (cf Rails)
        // ainsi on checkerait ici qu'il n'y pas plus de contract dans la table ayant pour id $old_contract_id.
        // Cela peut-il être couvert par l'option 'on_delete' ?
    }
    
    public function test_dependence()
    {
        // on teste ici l'effet de l'option 'on_delete' en chargeant un client dôté d'un
        // contrat, en le deletant et en vérifiant que le nb de contrat ds la table a diminué de 1.
    }
    
    public function test_assignment_before_parent_saved()
    {
        $client = new Client(array('name' => 'HP'));
        $contract = new Contract(array('code' => 'test', 'date' => '2005-12-01'));
        $contract->save();
        $client->contract = $contract;
        $this->assertTrue($client->is_new_record());
        $this->assertEqual($contract, $client->contract->target());
        $client->save();
        $this->assertFalse($client->is_new_record());
        $this->assertEqual($contract, $client->contract->target());
        $client2 = Client::$objects->get($client->id);
        $this->assertEqual($contract, $client2->contract->target());
    }
    
    public function test_create()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = $client->contract->create(array('code' => 'test'));
        $this->assertFalse($contract->is_new_record());
        $this->assertEqual($contract, $client->contract->target());
    }
    
    public function test_create_before_save()
    {
        $client = new Client(array('name' => 'Zend'));
        $contract = $client->contract->create(array('code' => 'test'));
        $this->assertEqual($contract, $client->contract->target());
        $this->assertFalse($contract->is_new_record());
        $this->assertTrue($client->is_new_record());
        $client->save();
        $this->assertEqual($contract, $client->contract->target());
        $this->assertFalse($contract->is_new_record());
        $this->assertFalse($client->is_new_record());
    }
}

?>
