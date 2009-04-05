<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

class HasOneTest extends ActiveTestCase
{
    public $fixtures = array('clients', 'contracts', 'projects');
    
    public function test_basic()
    {
        $client = Client::$objects->get(1);
        $contract = Contract::$objects->get(1);
        $client_contract = $client->contract->target();
        $this->assertEquals($contract, $client_contract);
        $this->assertEquals($contract->code, $client->contract->code);
    }
    
    public function test_type_mismatch()
    {
        $client = Client::$objects->get(1);
        try {
            $client->contract = Project::$objects->get(1);
        }
        catch (Exception $e) {
            $this->assertEquals('SAssociationTypeMismatch', get_class($e));
        }
    }
    
    public function test_natural_assignment()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = new Contract(array('code' => 'test'));
        $contract->save();
        $client->contract = $contract;
        $this->assertEquals($client->id, $contract->client_id);
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
        $this->assertEquals($contract, $client->contract->target());
        $client->save();
        $this->assertFalse($client->is_new_record());
        $this->assertEquals($contract, $client->contract->target());
    }
    
    public function test_create()
    {
        $client = new Client(array('name' => 'Zend'));
        $client->save();
        $contract = $client->contract->create(array('code' => 'test'));
        $this->assertFalse($contract->is_new_record());
        $this->assertEquals($contract, $client->contract->target());
    }
    
    public function test_create_before_save()
    {
        $client = new Client(array('name' => 'Zend'));
        $contract = $client->contract->create(array('code' => 'test'));
        $this->assertEquals($contract, $client->contract->target());
        $this->assertFalse($contract->is_new_record());
        $this->assertTrue($client->is_new_record());
        $client->save();
        $this->assertEquals($contract, $client->contract->target());
        $this->assertFalse($contract->is_new_record());
        $this->assertFalse($client->is_new_record());
    }
}
