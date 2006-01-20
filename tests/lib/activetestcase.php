<?php

require_once(CORE_DIR.'/model/model.php');
require_once(TESTS_DIR.'/core/fixtures/entities.php');

class ActiveTestCase extends UnitTestCase
{
    public $fixtures = array();
    public $useInstantiatedFixtures = False;
    
    private $loadedFixtures = array();
    private $fixtureInstances = array();
    
    public function __construct()
    {
        parent::UnitTestCase('ActiveEntity test');
        $this->loadedFixtures = Fixture::createFixtures(FIXTURES_DIR, $this->fixtures);
    }
    
    public function setUp()
    {
        $this->loadFixtures();
        if ($this->useInstantiatedFixtures) $this->instanciateFixtures();
    }
    
    public function tearDown()
    {
    
    }
    
    public function loadFixtures()
    {
        foreach($this->loadedFixtures as $table => $fixture)
        {
            $fixture->deleteExistingFixtures();
            $fixture->insertFixtures();
        }
    }
    
    public function instanciateFixtures()
    {
        foreach($this->loadedFixtures as $table => $fixture)
        {
            if (($insts = $fixture->instanciateFixtures()) !== false) $this->$table = $insts;
        }
    }
}

?>
