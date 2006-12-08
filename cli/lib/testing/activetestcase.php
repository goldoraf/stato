<?php

define('STATO_FIXTURES_DIR', STATO_CORE_PATH.'/model/test/fixtures');

require_once(STATO_CORE_PATH.'/model/model.php');
require_once(STATO_FIXTURES_DIR.'/models.php');

class ActiveTestCase extends StatoTestCase
{
    public $models = array();
    public $fixtures = array();
    public $use_instantiated_fixtures = False;
    
    private $loaded_fixtures = array();
    private $fixture_instances = array();
    
    public function __construct()
    {
        parent::UnitTestCase();
        $this->recreate_database();
        $this->loaded_fixtures = SFixture::create_fixtures(STATO_FIXTURES_DIR, $this->fixtures);
        foreach ($this->models as $class) 
            SActiveRecordMeta::add_manager_to_class(SInflection::camelize($class));
    }
    
    public function setUp()
    {
        $this->load_fixtures();
        if ($this->use_instantiated_fixtures) $this->instanciate_fixtures();
    }
    
    public function tearDown()
    {
    
    }
    
    public function load_fixtures()
    {
        foreach($this->loaded_fixtures as $table => $fixture)
        {
            $fixture->delete_existing_fixtures();
            $fixture->insert_fixtures();
        }
    }
    
    public function instanciate_fixtures()
    {
        foreach($this->loaded_fixtures as $table => $fixture)
        {
            if (($insts = $fixture->instanciate_fixtures()) !== false) $this->$table = $insts;
        }
    }
    
    private function recreate_database()
    {
        $db = SActiveRecord::connection();
        $dbname = $db->config['dbname'];
        $db->execute("DROP DATABASE IF EXISTS $dbname");
        $db->execute("CREATE DATABASE $dbname");
        $db->execute("USE $dbname");
        $sql = file_get_contents(STATO_FIXTURES_DIR.'/test_framework.sql');
        $requetes = explode(';', $sql);
        array_pop($requetes);
        foreach($requetes as $req) $db->execute($req);
    }
}

?>
