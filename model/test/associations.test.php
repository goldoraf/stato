<?php

class SBelongsToTest extends ActiveTestCase
{
    public $fixtures = array('profiles', 'employes');
    public $models = array('dependent_company_1', 'dependent_company_2', 'dependent_company_3');
    
    public function testBelongsTo()
    {
        $profile = Profile::$objects->get(1);
        $employe = Employe::$objects->get(1);
        $this->assertEqual($profile->employe->lastname, $employe->lastname);
    }
    
    public function testAssignment()
    {
        $profile = new Profile(array('cv'=>'GNU expert'));
        $employe = new Employe(array('lastname'=>'Richard', 'firstname'=>'Stallman'));
        $this->assertTrue($profile->employe->isNull());
        $profile->employe = $employe;
        $this->assertFalse($profile->employe->isNull());
        $profile->save();
        $this->assertEqual($employe->id, $profile->employe_id);
    }
    
    public function testNullAssignment()
    {
        $profile = Profile::$objects->get(1);
        $this->assertFalse($profile->employe->isNull());
        $profile->employe = null;
        $this->assertTrue($profile->employe->isNull());
        $profile->save();
        $profile2 = Profile::$objects->get(1);
        $this->assertTrue($profile2->employe->isNull());
    }
    
    public function testAssignmentBeforeParentSaved()
    {
        $profile = Profile::$objects->get(1);
        $employe = new Employe(array('lastname'=>'Max', 'firstname'=>'Payne'));
        $profile->employe = $employe;
        $this->assertEqual($profile->employe->lastname, $employe->lastname);
        $this->assertTrue($employe->isNewRecord());
        $profile->save();
        $this->assertFalse($employe->isNewRecord());
        $this->assertEqual($employe->id, $profile->employe_id);
    }
    
    public function testAssignmentBeforeChildSaved()
    {
        $employe = Employe::$objects->get(1);
        $profile = new Profile(array('cv'=>'Mozilla expert'));
        $profile->employe = $employe;
        $this->assertTrue($profile->isNewRecord());
        $profile->save();
        $this->assertFalse($profile->isNewRecord());
        $this->assertFalse($employe->isNewRecord());
        $this->assertEqual($employe->id, $profile->employe_id);
    }
    
    public function testAssignmentBeforeEitherSaved()
    {
        $employe = new Employe(array('lastname'=>'Max', 'firstname'=>'Payne'));
        $profile = new Profile(array('cv'=>'Lone private'));
        $profile->employe = $employe;
        $this->assertTrue($profile->isNewRecord());
        $this->assertTrue($employe->isNewRecord());
        $profile->save();
        $this->assertFalse($profile->isNewRecord());
        $this->assertFalse($employe->isNewRecord());
        $this->assertEqual($employe->id, $profile->employe_id);
    }
}

class SHasManyTest extends ActiveTestCase
{
    public $fixtures = array('companies', 'products');
    
    public function testAdd()
    {
        $company = Company::$objects->get(1);
        $product = new Product(array('name'=>'CD-R', 'price'=>'0.75'));
        $this->assertEqual(1, $company->products->count());
        $company->products->add($product);
        $this->assertEqual(2, $company->products->count());
    }
    
    public function testAddSubClass()
    {
        $company = Company::$objects->get(1);
        $product = new SuperProduct(array('name'=>'CD-R', 'price'=>'0.75'));
        $this->assertEqual(1, $company->products->count());
        $company->products->add($product);
        $this->assertEqual(2, $company->products->count());
    }
    
    public function testAddCollection()
    {
        $nb_companies = Company::$objects->count();
        $nb_products = Product::$objects->count();
        $new_company = new Company(array('name'=>'OpenSource Inc.'));
        $product1 = new Product(array('name'=>'mouse', 'price'=>'14.95'));
        $product2 = new Product(array('name'=>'screen', 'price'=>'350.00'));
        $new_company->products->add(array($product2, $product1));
        $this->assertTrue($new_company->isNewRecord());
        $this->assertTrue($product1->isNewRecord());
        $new_company->save();
        $this->assertFalse($new_company->isNewRecord());
        $this->assertFalse($product1->isNewRecord());
        $this->assertEqual($nb_companies+1, Company::$objects->count());
        $this->assertEqual($nb_products+2, Product::$objects->count());
        $company_reloaded = Company::$objects->get($new_company->id);
        $this->assertEqual(2, $company_reloaded->products->count());
    }
    
    public function testForeach()
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
    
    public function testCreate()
    {
        $company = Company::$objects->get(1);
        $nb_products = $company->products->count();
        $new_product = $company->products->create(array('name'=>'toaster', 'price'=>'15.00'));
        $this->assertEqual('toaster', $new_product->name);
        $this->assertFalse($new_product->isNewRecord());
        $company_reloaded = Company::$objects->get(1);
        $this->assertEqual($nb_products + 1, $company_reloaded->products->count());
    }
    
    public function testDeleteDependency()
    {
        $company = DependentCompany1::$objects->get(1);
        $company->delete();
        $this->assertEqual(0, Product::$objects->count());
    }
    
    public function testDeleteAllDependency()
    {
        $company = DependentCompany2::$objects->get(1);
        $company->delete();
        $this->assertEqual(0, Product::$objects->count());
    }
    
    public function testNullifyDependency()
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
    
    public function testHasManyJoinModel()
    {
        $comp = Company::$objects->get(1);
        $profiles = $comp->profiles->all()->dump();
        $this->assertEqual(2, count($profiles));
        $this->assertEqual('blablabla', $profiles[0]->cv);
        $this->assertEqual('xxx', $profiles[1]->cv);
    }
    
    public function testBelongsToJoinModel()
    {
    
    }
}

class SManyToManyTest extends ActiveTestCase
{
    public $fixtures = array('developers', 'projects', 'developers_projects');
    
    public function testBasic()
    {
        $ben = Developer::$objects->get(1);
        $this->assertEqual(2, $ben->projects->count());
        $proj = Project::$objects->get(1);
        $this->assertEqual(1, $proj->developers->count());
    }
    
    public function testAdd()
    {
        $richard = Developer::$objects->get(2);
        $proj = Project::$objects->get(1);
        $this->assertEqual(1, $richard->projects->count());
        $this->assertEqual(1, $proj->developers->count());
        $richard->projects->add($proj);
        $this->assertEqual(2, $richard->projects->count());
        $this->assertEqual(2, $proj->developers->count());
    }
    
    public function testAddCollection()
    {
    
    }
    
    public function testAddBeforeSave()
    {
        $nb_devels = Developer::$objects->count();
        $nb_projs  = Project::$objects->count();
        $peter = new Developer(array('name' => 'peter'));
        $proj1 = new Project(array('name' => 'WebNuked2.0'));
        $proj2 = new Project(array('name' => 'TotalWebInnov'));
        $peter->projects->add($proj1);
        $peter->projects->add($proj2);
        $this->assertTrue($peter->isNewRecord());
        $this->assertTrue($proj1->isNewRecord());
        $this->assertEqual(2, $peter->projects->count());
        $this->assertEqual($nb_projs, Project::$objects->count());
        $peter->save();
        $this->assertFalse($peter->isNewRecord());
        $this->assertFalse($proj1->isNewRecord());
        $this->assertEqual($nb_devels+1, Developer::$objects->count());
        $this->assertEqual($nb_projs+2, Project::$objects->count());
        $this->assertEqual(2, $peter->projects->count());
        $peter2 = Developer::$objects->get($peter->id);
        $this->assertEqual(2, $peter2->projects->count());
    }
    
    public function testCreate()
    {
        $richard = Developer::$objects->get(2);
        $proj = $richard->projects->create(array('name' => 'PlzNotAnotherRecursiveAcronym'));
        $projects = $richard->projects->all()->dump();
        $this->assertEqual($projects[1]->name, $proj->name);
        $this->assertFalse($proj->isNewRecord());
    }
    
    public function testDelete()
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
    
    public function testDeleteCollection()
    {
    
    }
    
    public function testClear()
    {
        $richard = Developer::$objects->get(2);
        $richard->projects->clear();
        $this->assertEqual(0, $richard->projects->count());
    }
}

class SHasOneTest extends ActiveTestCase
{
    public $fixtures = array('clients', 'contracts', 'projects');
    
    public function testBasic()
    {
        $client = Client::$objects->get(1);
        $contract = Contract::$objects->get(1);
        $this->assertCopy($contract, $client->contract->target());
        $this->assertEqual($contract->code, $client->contract->code);
    }
    
    public function testTypeMismatch()
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
    
    public function testNaturalAssignment()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = new Contract(array('code' => 'test'));
        $contract->save();
        $client->contract = $contract;
        $this->assertEqual($client->id, $contract->client_id);
    }
    
    public function testAssignmentToNull()
    {
        $client = Client::$objects->get(1);
        $this->assertFalse($client->contract->isNull());
        $client->contract = null;
        $client->save();
        $this->assertTrue($client->contract->isNull());
        $client2 = Client::$objects->get(1);
        $this->assertTrue($client2->contract->isNull());
        // il faudrait rendre la classe Contract 'dependent' de la classe Client (cf Rails)
        // ainsi on checkerait ici qu'il n'y pas plus de contract dans la table ayant pour id $old_contract_id.
        // Cela peut-il être couvert par l'option 'on_delete' ?
    }
    
    public function testDependence()
    {
        // on teste ici l'effet de l'option 'on_delete' en chargeant un client dôté d'un
        // contrat, en le deletant et en vérifiant que le nb de contrat ds la table a diminué de 1.
    }
    
    public function testAssignmentBeforeParentSaved()
    {
        $client = new Client(array('name' => 'HP'));
        $contract = new Contract(array('code' => 'test', 'date' => '2005-12-01'));
        $contract->save();
        $client->contract = $contract;
        $this->assertTrue($client->isNewRecord());
        $this->assertEqual($contract, $client->contract->target());
        $client->save();
        $this->assertFalse($client->isNewRecord());
        $this->assertEqual($contract, $client->contract->target());
        $client2 = Client::$objects->get($client->id);
        $this->assertEqual($contract, $client2->contract->target());
    }
    
    public function testCreate()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = $client->contract->create(array('code' => 'test'));
        $this->assertFalse($contract->isNewRecord());
        $this->assertEqual($contract, $client->contract->target());
    }
    
    public function testCreateBeforeSave()
    {
        $client = new Client(array('name' => 'Zend'));
        $contract = $client->contract->create(array('code' => 'test'));
        $this->assertEqual($contract, $client->contract->target());
        $this->assertFalse($contract->isNewRecord());
        $this->assertTrue($client->isNewRecord());
        $client->save();
        $this->assertEqual($contract, $client->contract->target());
        $this->assertFalse($contract->isNewRecord());
        $this->assertFalse($client->isNewRecord());
    }
}

?>
