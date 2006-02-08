<?php

class SBelongsToTest extends ActiveTestCase
{
    public $fixtures = array('profiles', 'employes');
    
    function testBelongsTo()
    {
        $profile = SActiveStore::findByPk('Profile', 1);
        $employe = $profile->employe;
        $employeBis = SActiveStore::findByPk('Employe', 1);
        $this->assertEqual($employe->lastname, $employeBis->lastname);
    }
    
    function testBuild()
    {
        $profile = new Profile();
        $profile->cv = 'test';
        $employe = $profile->buildEmploye(array('lastname'=>'Bruce', 'firstname'=>'Wayne'));
        $employe->lastname = 'Brice';
        $this->assertEqual($employe, $profile->employe);
        $profile->save();
        $this->assertEqual($employe->id, $profile->employe_id);
        $this->assertEqual($employe, $profile->employe);
    }
    
    function testCreate()
    {
        $profile = new Profile();
        $profile->cv = 'test';
        $employe = $profile->createEmploye(array('lastname'=>'Bruce', 'firstname'=>'Wayne'));
        $this->assertEqual($employe, $profile->employe);
        $profile->save();
        $this->assertEqual('Bruce', $employe->lastname);
        $this->assertEqual($employe, $profile->employe);
        $this->assertEqual($employe->id, $profile->employe_id);
    }
    
    function testNaturalAssignment()
    {
        $profile = new Profile(array('cv'=>'GNU expert'));
        $employe = new Employe(array('lastname'=>'Richard', 'firstname'=>'Stallman'));
        $profile->employe = $employe;
        $this->assertEqual($employe->id, $profile->employe_id);
    }
    
    function testAssignmentBeforeParentSaved()
    {
        $profile = SActiveStore::findByPk('Profile', 1);
        $employe = new Employe(array('lastname'=>'Max', 'firstname'=>'Payne'));
        $profile->employe = $employe;
        $this->assertEqual($employe, $profile->employe);
        $this->assertTrue($employe->isNewRecord());
        $profile->save();
        $employe->save();
        $this->assertFalse($employe->isNewRecord());
        $this->assertEqual($employe, $profile->employe);
        $this->assertEqual($employe, $profile->employe(True));
    }
    
    function testAssignmentBeforeChildSaved()
    {
        $employe = SActiveStore::findByPk('Employe', 1);
        $profile = new Profile(array('cv'=>'Mozilla expert'));
        $profile->employe = $employe;
        $this->assertTrue($profile->isNewRecord());
        $profile->save();
        $this->assertFalse($profile->isNewRecord());
        $this->assertFalse($employe->isNewRecord());
        $this->assertEqual($employe, $profile->employe);
        $this->assertEqual($employe, $profile->employe(True));
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
        $this->assertEqual($employe, $profile->employe);
        $this->assertEqual($employe, $profile->employe(True));
    }
}

class SHasManyTest extends ActiveTestCase
{
    public $fixtures = array('companies', 'products');
    
    function testAdd()
    {
        $company = SActiveStore::findByPk('Company', 1);
        $product = new Product(array('name'=>'CD-R', 'price'=>'0.75'));
        $company->products[] = $product;
        //$this->assertEqual(2, count($company->products));
        $this->assertEqual(2, $company->countProducts());
        $company->save();
        $companyReloaded = SActiveStore::findByPk('Company', 1);
        //$this->assertEqual(2, count($companyReloaded->products));
        $this->assertEqual(2, $companyReloaded->countProducts());
    }
    
    function testAddCollection()
    {
    
    }
    
    function testAddBeforeSave()
    {
        $nb_companies = SActiveStore::count('Company');
        $nb_products = SActiveStore::count('Product');
        $new_company = new Company(array('name'=>'OpenSource Inc.'));
        $product1 = new Product(array('name'=>'mouse', 'price'=>'14.95'));
        $new_company->products[] = $product1;
        $product2 = new Product(array('name'=>'screen', 'price'=>'350.00'));
        $new_company->products[] = $product2;
        $this->assertTrue($new_company->isNewRecord());
        $this->assertTrue($product1->isNewRecord());
        $this->assertEqual($nb_companies, SActiveStore::count('Company'));
        $this->assertEqual($nb_products, SActiveStore::count('Product'));
        $this->assertTrue($new_company->save());
        $this->assertFalse($new_company->isNewRecord());
        $this->assertFalse($product1->isNewRecord());
        $this->assertEqual($nb_companies+1, SActiveStore::count('Company'));
        $this->assertEqual($nb_products+2, SActiveStore::count('Product'));
        $this->assertEqual(2, $new_company->countProducts());
        $company_reloaded = SActiveStore::findByPk('Company', $new_company->id);
        $this->assertEqual(2, $company_reloaded->countProducts());
    }
    
    function testForeach()
    {
        $new_company = new Company(array('name'=>'MegaGeek corp.'));
        $new_company->products[] = new Product(array('name'=>'usb key', 'price'=>'34.95'));
        $new_company->products[] = new Product(array('name'=>'keyboard', 'price'=>'50.00'));
        $i = 0;
        foreach($new_company->products as $product)
        {
            $this->assertEqual($product, $new_company->products[$i]);
            $i++;
        }
    }
    
    function testBuild()
    {
        $company = SActiveStore::findByPk('Company', 1);
        $new_product = $company->buildProducts(array('name'=>'toaster', 'price'=>'15.00'));
        $this->assertEqual('toaster', $new_product->name);
        $this->assertTrue($new_product->isNewRecord());
        $nb_products = $company->countProducts();
        $offset = $nb_products - 1; // crash si on écrit $company->products[$nb_products - 1]  !!!!!
        $this->assertEqual($new_product, $company->products[$offset]);
        $this->assertTrue($new_product->save());
        $this->assertFalse($new_product->isNewRecord());
        $company->products(True);
        $this->assertEqual($nb_products, $company->countProducts());
    }
    
    function testInvalidBuild()
    {
        $company = SActiveStore::findByPk('Company', 1);
        $new_product = $company->buildProducts();
        $this->assertTrue($new_product->isNewRecord());
        $this->assertFalse($new_product->isValid());
        $nb_products = $company->countProducts();
        $offset = $nb_products - 1; // crash si on écrit $company->products[$nb_products - 1]  !!!!!
        $this->assertEqual($new_product, $company->products[$offset]);
        $this->assertFalse($new_product->save());
        $company->products(True);
        $this->assertEqual($nb_products - 1, $company->countProducts());
    }
}

class SManyToManyTest extends ActiveTestCase
{
    public $fixtures = array('developers', 'projects', 'developers_projects');
    
    function testBasic()
    {
        $ben = SActiveStore::findByPk('Developer', 1);
        $this->assertEqual(2, $ben->countProjects());
        $proj = SActiveStore::findByPk('Project', 2);
        $this->assertEqual(2, $proj->countDevelopers());
    }
    
    function testAdd()
    {
        $richard = SActiveStore::findByPk('Developer', 2);
        $proj = SActiveStore::findByPk('Project', 1);
        $this->assertEqual(1, $richard->countProjects());
        $this->assertEqual(1, $proj->countDevelopers());
        $richard->projects[] = $proj;
        $this->assertEqual(2, $richard->countProjects());
        $richard->projects(True);
        $this->assertEqual(2, $richard->countProjects());
        $proj->developers(True);
        $this->assertEqual(2, $proj->countDevelopers());
    }
    
    function testAddCollection()
    {
    
    }
    
    function testAddBeforeSave()
    {
        $nb_devels = SActiveStore::count('Developer');
        $nb_projs  = SActiveStore::count('Project');
        $peter = new Developer(array('name' => 'peter'));
        $proj1 = new Project(array('name' => 'WebNuked2.0'));
        $proj2 = new Project(array('name' => 'TotalWebInnov'));
        $peter->projects[] = $proj1;
        $peter->projects[] = $proj2;
        $this->assertTrue($peter->isNewRecord());
        $this->assertTrue($proj1->isNewRecord());
        $peter->save();
        $this->assertFalse($peter->isNewRecord());
        $this->assertEqual($nb_devels+1, SActiveStore::count('Developer'));
        $this->assertEqual($nb_projs+2, SActiveStore::count('Project'));
        $this->assertEqual(2, $peter->countProjects());
        $peter->projects(True);
        $this->assertEqual(2, $peter->countProjects());
    }
    
    function testBuild()
    {
        $richard = SActiveStore::findByPk('Developer', 2);
        $proj = $richard->buildProjects(array('name' => 'BlueProjectOfDeath'));
        $this->assertEqual($richard->projects[1], $proj);
        $this->assertTrue($proj->isNewRecord());
        $richard->save();
        $this->assertFalse($proj->isNewRecord());
        $this->assertEqual($richard->projects[1], $proj);
    }
    
    function testCreate()
    {
        $richard = SActiveStore::findByPk('Developer', 2);
        $proj = $richard->createProjects(array('name' => 'PlzNotAnotherRecursiveAcronym'));
        $this->assertEqual($richard->projects[1], $proj);
        $this->assertFalse($proj->isNewRecord());
    }
    
    function testDelete()
    {
        $ben = SActiveStore::findByPk('Developer', 1);
        $proj = SActiveStore::findByPk('Project', 1);
        $this->assertEqual(2, $ben->countProjects());
        $this->assertEqual(1, $proj->countDevelopers());
        $ben->deleteProjects($proj);
        $this->assertEqual(1, $ben->countProjects());
        $ben->projects(True);
        $this->assertEqual(1, $ben->countProjects());
        $proj->developers(True);
        $this->assertEqual(0, $proj->countDevelopers());
    }
    
    function testDeleteCollection()
    {
    
    }
    
    function testClear()
    {
        $richard = SActiveStore::findByPk('Developer', 2);
        $richard->clearProjects();
        $this->assertEqual(0, $richard->countProjects());
        $richard->projects(True);
        //$this->assertEqual(0, $richard->countProjects());
        // clear() n'est pas censé détruire les objets dans le cas d'une assoc ManyToMany
        // les tests de Rails font pourtant comme si...
    }
}

class SOneToOneTest extends ActiveTestCase
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
}

?>
