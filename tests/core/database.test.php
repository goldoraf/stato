<?php

require_once(CORE_DIR.'/model/model.php');

class SDatabaseTest extends UnitTestCase
{
    function testConnection()
    {
        $db1 = SDatabase::getInstance();
        $db2 = SDatabase::getInstance();
        $this->assertReference($db1, $db2);
    }
    
    function testRecreate()
    {
        $db = SDatabase::getInstance();
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
