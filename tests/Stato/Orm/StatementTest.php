<?php

namespace Stato\Orm;

require_once __DIR__ . '/../TestsHelper.php';

require_once 'Stato/Orm/Expression.php';

class StatementTest extends TestCase
{
    protected $fixtures = array('users_light');
    
    public function setup()
    {
        parent::setup();
        $this->users = new Table('users_light', array(
            new Column('id', Column::INTEGER, array('primary_key' => true, 'auto_increment' => true)),
            new Column('fullname', Column::STRING),
            new Column('login', Column::STRING),
            new Column('password', Column::STRING)
        ));
    }
    
    public function testInsert()
    {
        $ins = $this->users->insert()->values(array('fullname' => 'Foo Bar', 'login' => 'foo', 'password' => 'bar'));
        $res = $this->connection->execute($ins);
        $this->assertEquals(3, $res->lastInsertId());
        $this->assertEquals(1, $res->rowCount());
    }
    
    public function testSelect()
    {
        $res = $this->connection->execute($this->users->select());
        $this->assertEquals(2, $res->rowCount());
        $this->assertEquals(array('id' => 1, 'fullname' => 'John Doe', 'login' => 'jdoe', 'password' => 'john'), $res->fetch());
        $this->assertEquals(array('id' => 2, 'fullname' => 'Jane Doe', 'login' => 'jane', 'password' => 'psss'), $res->fetch());
        
        $res = $this->connection->execute(select(array($this->users->fullname, $this->users->login)));
        $users = array();
        foreach ($res as $row) $users[] = $row;
        $this->assertEquals(array(array('fullname' => 'John Doe', 'login' => 'jdoe'), array('fullname' => 'Jane Doe', 'login' => 'jane')), $users);
        
        $res = $this->connection->execute($this->users->select()->where($this->users->id->eq(1)));
        $this->assertEquals(1, $res->rowCount());
        $this->assertEquals(array('id' => 1, 'fullname' => 'John Doe', 'login' => 'jdoe', 'password' => 'john'), $res->fetch());
        
        $res = $this->connection->execute($this->users->select()->where($this->users->login->like('jdoe')));
        $this->assertEquals(1, $res->rowCount());
        $this->assertEquals(array('id' => 1, 'fullname' => 'John Doe', 'login' => 'jdoe', 'password' => 'john'), $res->fetch());
    }
    
    public function testUpdate()
    {
        $res = $this->connection->execute($this->users->update()->values(array('password' => 'password')));
        $this->assertEquals(2, $res->rowCount());
        $res = $this->connection->execute(select(array($this->users->login, $this->users->password)));
        $this->assertEquals(array(array('login' => 'jdoe', 'password' => 'password'), array('login' => 'jane', 'password' => 'password')),
                            $res->fetchAll());
        
        $res = $this->connection->execute(
            $this->users->update()->values(array('fullname' => 'John I. Doe jr.'))->where($this->users->fullname->like('John Doe'))
        );
        $this->assertEquals(1, $res->rowCount());
        $res = $this->connection->execute(select(array($this->users->fullname)));
        $this->assertEquals(array(array('fullname' => 'John I. Doe jr.'), array('fullname' => 'Jane Doe')), $res->fetchAll());
    }
    
    public function testDelete()
    {
        $res = $this->connection->execute($this->users->delete()->where($this->users->login->eq('jdoe')));
        $this->assertEquals(1, $res->rowCount());
    }
}
