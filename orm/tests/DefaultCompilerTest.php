<?php

require_once dirname(__FILE__) . '/../../tests/TestsHelper.php';

require_once 'connection.php';
require_once 'expression.php';
require_once 'schema.php';
require_once 'compiler.php';
require_once 'helpers.php';

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
            new Stato_Column('user_id', Stato_Column::INTEGER, array('foreign_key' => 'users.id')),
            new Stato_Column('email_address', Stato_Column::STRING),
        ));
    }
    
    public function testTableClause()
    {
        $this->assertEquals('users', $this->users->__toString());
    }
    
    public function testInsert()
    {
        $this->assertEquals(
            'INSERT INTO users (id, firstname, lastname) VALUES (:id, :firstname, :lastname)',
            $this->users->insert()->__toString()
        );
        $this->assertEquals(
            'INSERT INTO users (firstname, lastname) VALUES (:firstname, :lastname)',
            $this->users->insert()->values(array('firstname' => 'John', 'lastname' => 'Doe'))->__toString()
        );
        $this->setExpectedException('Stato_UnknownColumn');
        $this->users->insert()->values(array('foo' => 'bar'))->__toString();
    }
    
    public function testSelect()
    {
        $this->assertEquals(
            'SELECT users.id, users.firstname, users.lastname FROM users',
            $this->users->select()->__toString()
        );
        $this->assertEquals(
            'SELECT users.firstname, users.lastname FROM users',
            $this->users->select(array('firstname', 'lastname'))->__toString()
        );
        $this->assertEquals(
            'SELECT users.firstname, users.lastname FROM users',
            $this->users->select(array($this->users->firstname, $this->users->lastname))->__toString()
        );
        $this->assertEquals(
            'SELECT users.id, users.firstname, users.lastname, addresses.user_id, addresses.email_address FROM users, addresses',
            (string) new Stato_Select(array($this->users, $this->addresses))
        );
    }
    
    public function testAlias()
    {
        $this->assertEquals(
            'SELECT u.firstname, u.lastname FROM users AS u',
            $this->users->alias('u')->select(array('firstname', 'lastname'))->__toString()
        );
        $u = $this->users->alias('u');
        $this->assertEquals(
            'SELECT u.firstname, u.lastname FROM users AS u',
            (string) new Stato_Select(array($u->firstname, $u->lastname))
        );
    }
    
    public function testOperators()
    {
        $this->assertEquals('users.id FOO :id_1', $this->users->id->op('FOO', 1)->__toString());
        $this->assertEquals('users.id = :id_1', $this->users->id->eq(1)->__toString());
        $this->assertEquals('users.id = addresses.user_id', $this->users->id->eq($this->addresses->user_id)->__toString());
        $this->assertEquals('users.id IS NULL', $this->users->id->eq(null)->__toString());
        $this->assertEquals('users.id != :id_1', $this->users->id->ne(1)->__toString());
        $this->assertEquals('users.id < :id_1', $this->users->id->lt(1)->__toString());
        $this->assertEquals('users.id <= :id_1', $this->users->id->le(1)->__toString());
        $this->assertEquals('users.id > :id_1', $this->users->id->gt(1)->__toString());
        $this->assertEquals('users.id >= :id_1', $this->users->id->ge(1)->__toString());
        $this->assertEquals('users.lastname LIKE :lastname_1', $this->users->lastname->like('D%')->__toString());
        
        $compiled = $this->users->lastname->startswith('D')->compile();
        $this->assertEquals('users.lastname LIKE :lastname_1', $compiled->__toString());
        $this->assertEquals(array(':lastname_1' => 'D%'), $compiled->params);
        $compiled = $this->users->lastname->endswith('D')->compile();
        $this->assertEquals('users.lastname LIKE :lastname_1', $compiled->__toString());
        $this->assertEquals(array(':lastname_1' => '%D'), $compiled->params);
        $compiled = $this->users->lastname->contains('D')->compile();
        $this->assertEquals('users.lastname LIKE :lastname_1', $compiled->__toString());
        $this->assertEquals(array(':lastname_1' => '%D%'), $compiled->params);
    }
    
    public function testOperatorsNegate()
    {
        $this->assertEquals('NOT (users.id FOO :id_1)', not_($this->users->id->op('FOO', 1))->__toString());
        $this->assertEquals('users.id != :id_1', not_($this->users->id->eq(1))->__toString());
        $this->assertEquals('users.id IS NOT NULL', not_($this->users->id->eq(null))->__toString());
        $this->assertEquals('users.id = :id_1', not_($this->users->id->ne(1))->__toString());
        $this->assertEquals('users.id >= :id_1', not_($this->users->id->lt(1))->__toString());
        $this->assertEquals('users.id > :id_1', not_($this->users->id->le(1))->__toString());
        $this->assertEquals('users.id <= :id_1', not_($this->users->id->gt(1))->__toString());
        $this->assertEquals('users.id < :id_1', not_($this->users->id->ge(1))->__toString());
        $this->assertEquals('users.lastname NOT LIKE :lastname_1', not_($this->users->lastname->like('D%'))->__toString());
    }
    
    public function testBindParams()
    {
        $compiled = $this->users->id->eq(1)->compile();
        $this->assertEquals('users.id = :id_1', $compiled->__toString());
        $this->assertEquals(array(':id_1' => 1), $compiled->params);
    }
    
    public function testConjonctions()
    {
        $or = or_($this->addresses->email_address->eq('john@doe.net'), $this->addresses->email_address->eq('doe@john.net'));
        $this->assertEquals('addresses.email_address = :email_address_1 OR addresses.email_address = :email_address_2', $or->compile()->__toString());
        $this->assertEquals(array(':email_address_1' => 'john@doe.net', ':email_address_2' => 'doe@john.net'), $or->compile()->params);
        $exp = and_($this->users->firstname->eq('John'), $this->users->lastname->eq('Doe'), $or, not_($this->users->id->gt(5)));
        $this->assertEquals('users.firstname = :firstname_1 AND users.lastname = :lastname_1 AND (addresses.email_address = :email_address_1 OR addresses.email_address = :email_address_2) AND users.id <= :id_1', $exp->compile()->__toString());
        $this->assertEquals(array(':email_address_1' => 'john@doe.net', ':email_address_2' => 'doe@john.net', ':firstname_1' => 'John', ':lastname_1' => 'Doe', ':id_1' => 5), $exp->compile()->params);
    }
    
    public function testJoins()
    {
        $this->assertEquals('users JOIN addresses ON addresses.email_address LIKE users.firstname',
            $this->users->join($this->addresses, $this->addresses->email_address->like($this->users->firstname))->__toString());
        $this->assertEquals('users LEFT OUTER JOIN addresses ON addresses.email_address LIKE users.firstname',
            $this->users->join($this->addresses, $this->addresses->email_address->like($this->users->firstname), true)->__toString());
        $this->assertEquals('users JOIN addresses ON users.id = addresses.user_id',
            $this->users->join($this->addresses)->__toString());
    }
}