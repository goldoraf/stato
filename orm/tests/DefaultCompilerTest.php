<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'connection.php';
require_once 'column.php';
require_once 'table.php';
require_once 'statement.php';
require_once 'compiler.php';

class Stato_DefaultCompilerTest extends PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->users = new Stato_Table('users', array(
            new Stato_Column('id', Stato_Column::INTEGER),
            new Stato_Column('firstname', Stato_Column::STRING),
            new Stato_Column('lastname', Stato_Column::STRING),
        ));
         $this->addresses = new Stato_Table('addresses', array(
            new Stato_Column('user_id', Stato_Column::INTEGER),
            new Stato_Column('email_address', Stato_Column::STRING),
        ));
    }
    
    public function testInsert()
    {
        $this->assertEquals(
            'INSERT INTO users (id, firstname, lastname) VALUES (:id, :firstname, :lastname)',
            (string) $this->users->insert()
        );
        $this->assertEquals(
            'INSERT INTO users (firstname, lastname) VALUES (:firstname, :lastname)',
            (string) $this->users->insert()->values(array('firstname' => 'John', 'lastname' => 'Doe'))
        );
        $this->setExpectedException('Stato_UnknownColumn');
        $this->users->insert()->values(array('foo' => 'bar'))->__toString();
    }
    
    public function testSelect()
    {
        $this->assertEquals(
            'SELECT users.id, users.firstname, users.lastname FROM users',
            (string) $this->users->select()
        );
        $this->assertEquals(
            'SELECT users.firstname, users.lastname FROM users',
            (string) $this->users->select(array('firstname', 'lastname'))
        );
        $this->assertEquals(
            'SELECT users.firstname, users.lastname FROM users',
            (string) $this->users->select(array($this->users->firstname, $this->users->lastname))
        );
        $this->assertEquals(
            'SELECT users.id, users.firstname, users.lastname, addresses.user_id, addresses.email_address FROM users, addresses',
            (string) new Stato_Select(array($this->users, $this->addresses))
        );
    }
}