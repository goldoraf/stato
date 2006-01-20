<?php

require_once(CORE_DIR.'/model/model.php');

class DatabaseTest extends UnitTestCase
{
    function DatabaseTest()
    {
        $this->UnitTestCase('Database class test');
    }
    
    function testConnection()
    {
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();
        $this->assertReference($db1, $db2);
    }
    
    function testRecreate()
    {
        $db = Database::getInstance();
        $this->assertTrue($db->execute('DROP DATABASE IF EXISTS test_framework'));
        $this->assertTrue($db->execute('CREATE DATABASE test_framework'));
        $this->assertTrue($db->execute('USE test_framework'));
        $sql = file_get_contents(TESTS_DIR.'/core/fixtures/test_framework.sql');
        $requetes = explode(';', $sql);
        array_pop($requetes);
        foreach($requetes as $req) $db->execute($req);
    }
}

?>
