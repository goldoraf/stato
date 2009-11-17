<?php

namespace Stato\Orm;

require_once __DIR__ . '/../TestsHelper.php';

require_once 'Stato/Orm/Expression.php';

class StatementTest extends TestCase
{
    public function setup()
    {
        parent::setup();
        $this->users = self::$tables['users'];
    }
    
    public function testInsert()
    {
        $ins = $this->users->insert()->values(array('fullname' => 'John Doe', 'login' => 'jdoe', 'password' => 'test'));
        $res = $this->connection->execute($ins);
        $this->assertEquals(1, $res->lastInsertId());
        $this->assertEquals(1, $res->rowCount());
    }
}