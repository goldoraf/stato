<?php

class SBelongsToTest extends ActiveTestCase
{
    public $fixtures = array('profiles', 'employes');
    public $models = array('dependent_company_1', 'dependent_company_2', 'dependent_company_3');
    
    function testBelongsTo()
    {
        $profile = Profile::$objects->get(1);
        $employe = Employe::$objects->get(1);
        $this->assertEqual($profile->employe->lastname, $employe->lastname);
    }
    
    function testAssignment()
    {
        $profile = new Profile(array('cv'=>'GNU expert'));
        $employe = new Employe(array('lastname'=>'Richard', 'firstname'=>'Stallman'));
        $profile->employe = $employe;
        $profile->save();
        $this->assertEqual($employe->id, $profile->employe_id);
    }
    
    function testAssignmentBeforeParentSaved()
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
    
    function testAssignmentBeforeChildSaved()
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
    
    function testAssignmentBeforeEitherSaved()
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
    
    function testAdd()
    {
        $company = Company::$objects->get(1);
        $product = new Product(array('name'=>'CD-R', 'price'=>'0.75'));
        $this->assertEqual(1, $company->products->count());
        $company->products->add($product);
        $this->assertEqual(2, $company->products->count());
    }
    
    function testAddCollection()
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
    
    function testForeach()
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
    
    function testCreate()
    {
        $company = Company::$objects->get(1);
        $nb_products = $company->products->count();
        $new_product = $company->products->create(array('name'=>'toaster', 'price'=>'15.00'));
        $this->assertEqual('toaster', $new_product->name);
        $this->assertFalse($new_product->isNewRecord());
        $company_reloaded = Company::$objects->get(1);
        $this->assertEqual($nb_products + 1, $company_reloaded->products->count());
    }
    
    function testDeleteDependency()
    {
        $company = DependentCompany1::$objects->get(1);
        $company->delete();
        $this->assertEqual(0, Product::$objects->count());
    }
    
    function testDeleteAllDependency()
    {
        $company = DependentCompany2::$objects->get(1);
        $company->delete();
        $this->assertEqual(0, Product::$objects->count());
    }
    
    function testNullifyDependency()
    {
        $company = DependentCompany3::$objects->get(1);
        $company->delete();
        $this->assertEqual(1, Product::$objects->count());
        $product = Product::$objects->get(1);
        $this->assertNull($product->company_id);
    }
}

class SManyToManyTest extends ActiveTestCase
{
    public $fixtures = array('developers', 'projects', 'developers_projects');
    
    function testBasic()
    {
        $ben = Developer::$objects->get(1);
        $this->assertEqual(2, $ben->projects->count());
        $proj = Project::$objects->get(1);
        $this->assertEqual(1, $proj->developers->count());
    }
    
    function testAdd()
    {
        $richard = Developer::$objects->get(2);
        $proj = Project::$objects->get(1);
        $this->assertEqual(1, $richard->projects->count());
        $this->assertEqual(1, $proj->developers->count());
        $richard->projects->add($proj);
        $this->assertEqual(2, $richard->projects->count());
        $this->assertEqual(2, $proj->developers->count());
    }
    
    function testAddCollection()
    {
    
    }
    
    function testAddBeforeSave()
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
        $this->assertEqual(0, $peter->projects->count());
        $peter->save();
        $this->assertFalse($peter->isNewRecord());
        $this->assertFalse($proj1->isNewRecord());
        $this->assertEqual($nb_devels+1, Developer::$objects->count());
        $this->assertEqual($nb_projs+2, Project::$objects->count());
        $this->assertEqual(2, $peter->projects->count());
        $peter2 = Developer::$objects->get($peter->id);
        $this->assertEqual(2, $peter2->projects->count());
    }
    
    function testCreate()
    {
        $richard = Developer::$objects->get(2);
        $proj = $richard->projects->create(array('name' => 'PlzNotAnotherRecursiveAcronym'));
        $projects = $richard->projects->all()->dump();
        $this->assertEqual($projects[1]->name, $proj->name);
        $this->assertFalse($proj->isNewRecord());
    }
    
    function testDelete()
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
    
    function testDeleteCollection()
    {
    
    }
    
    function testClear()
    {
        $richard = Developer::$objects->get(2);
        $richard->projects->clear();
        $this->assertEqual(0, $richard->projects->count());
    }
}

/*class SHasOneTest extends ActiveTestCase
{
    public $fixtures = array('clients', 'contracts');
    
    function testBasic()
    {
        $client = SActiveStore::findByPk('Client', 1);
        $contract = SActiveStore::findByPk('Contract', 1);
        $this->assertCopy($contract, $client->contract);
        $this->assertEqual($contract->code, $client->contract->code);
    }
    
    function testTypeMismatch()
    {
        $client = SActiveStore::findByPk('Client', 1);
        try
        {
            $client->contract = 1;
            $client->contract = SActiveStore::findByPk('Project', 1);
        }
        catch (Exception $e)
        {
            $this->assertEqual('SAssociationTypeMismatch', get_class($e));
        }
    }
    
    function testNaturalAssignment()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = new Contract(array('code' => 'test'));
        $contract->save();
        $client->contract = $contract;
        $this->assertEqual($client->id, $contract->client_id);
    }
    
    function testAssignmentToNull()
    {
        $client = SActiveStore::findByPk('Client', 1);
        $old_contract_id = $client->contract->id;
        $client->contract = Null;
        $client->save();
        $this->assertNull($client->contract);
        // il faudrait rendre la classe Contract 'dependent' de la classe Client (cf Rails)
        // ainsi on checkerait ici qu'il n'y pas plus de contract dans la table ayant pour id $old_contract_id.
        // Cela peut-il être couvert par l'option 'on_delete' ?
    }
    
    function testDependence()
    {
        // on teste ici l'effet de l'option 'on_delete' en chargeant un client dôté d'un
        // contrat, en le deletant et en vérifiant que le nb de contrat ds la table a diminué de 1.
    }
    
    function testAssignmentBeforeParentSaved()
    {
        $client = new Client(array('name' => 'HP'));
        $contract = new Contract(array('code' => 'test', 'date' => '2005-12-01'));
        $contract->save();
        $client->contract = $contract;
        $this->assertTrue($client->isNewRecord());
        $this->assertEqual($contract, $client->contract);
        $this->assertTrue($client->save());
        $this->assertEqual($contract, $client->contract);
        $this->assertCopy($contract, $client->contract(True)); // assertEqual crashes 
        // because of a nesting level too deep - recursive dependency
    }
    
    function testBuild()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = $client->buildContract(array('code' => 'test'));
        $this->assertEqual($contract, $client->contract);
        $this->assertTrue($contract->save());
        $this->assertEqual($contract, $client->contract);
    }
    
    function testFailingBuild()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = $client->buildContract();
        $this->assertEqual($contract, $client->contract);
        $this->assertFalse($contract->save());
        $this->assertEqual($contract, $client->contract);
        $this->assertEqual('ERR_VALID_REQUIRED', $contract->errors['code']);
    }
    
    function testBuildBeforeChildSaved()
    {
        $client = SActiveStore::findByPk('Client', 3); // client without contract
        $contract = $client->buildContract(array('code' => 'test'));
        $this->assertEqual($contract, $client->contract);
        $this->assertTrue($contract->isNewRecord());
        $this->assertTrue($client->save());
        $this->assertFalse($contract->isNewRecord());
        $this->assertEqual($contract, $client->contract);
    }
    
    function testBuildBeforeEitherSaved()
    {
        $client = new Client(array('name' => 'Zend'));
        $contract = $client->buildContract(array('code' => 'test'));
        $this->assertEqual($contract, $client->contract);
        $this->assertTrue($contract->isNewRecord());
        $this->assertTrue($client->save());
        $this->assertFalse($contract->isNewRecord());
        $this->assertEqual($contract, $client->contract);
    }
    
    function testCreate()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = $client->createContract(array('code' => 'test'));
        $this->assertFalse($contract->isNewRecord());
        $this->assertEqual($contract, $client->contract);
    }
    
    function testCreateBeforeSave()
    {
        $client = new Client(array('name' => 'Zend'));
        $contract = $client->createContract(array('code' => 'test'));
        $this->assertEqual($contract, $client->contract);
        $this->assertFalse($contract->isNewRecord());
        $this->assertTrue($client->isNewRecord());
        $this->assertTrue($client->save());
        $this->assertEqual($contract, $client->contract);
        $this->assertFalse($contract->isNewRecord());
        $this->assertFalse($client->isNewRecord());
    }
}*/

?>
