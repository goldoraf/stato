<?php

define('FIXTURES_DIR', CORE_DIR.'/model/test/fixtures');

require_once(CORE_DIR.'/model/model.php');
require_once(FIXTURES_DIR.'/models.php');

class ActiveTestCase extends UnitTestCase
{
    public $fixtures = array();
    public $useInstantiatedFixtures = False;
    
    private $loadedFixtures = array();
    private $fixtureInstances = array();
    
    public function __construct()
    {
        parent::UnitTestCase();
        $this->recreateDatabase();
        $this->loadedFixtures = SFixture::createFixtures(FIXTURES_DIR, $this->fixtures);
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
    
    private function recreateDatabase()
    {
        $db = SDatabase::getInstance();
        $dbname = $db->config['dbname'];
        $db->execute("DROP DATABASE IF EXISTS $dbname");
        $db->execute("CREATE DATABASE $dbname");
        $db->execute("USE $dbname");
        $sql = file_get_contents(FIXTURES_DIR.'/test_framework.sql');
        $requetes = explode(';', $sql);
        array_pop($requetes);
        foreach($requetes as $req) $db->execute($req);
    }
}

?>
