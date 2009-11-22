<?php

namespace Stato\Orm;

require_once __DIR__ . '/../TestsHelper.php';

require_once 'Stato/Orm/Schema.php';

class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->users = new Table('users', array(
            new Column('id', Column::INTEGER),
            new Column('firstname', Column::STRING),
            new Column('lastname', Column::STRING),
        ));
         $this->addresses = new Table('addresses', array(
            new Column('user_id', Column::INTEGER, array('foreign_key' => 'users.id')),
            new Column('email_address', Column::STRING),
        ));
    }
    
    public function testTableClause()
    {
        $this->assertEquals('users', $this->users->__toString());
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
        $this->assertEquals('users.id IN (:id_1,:id_2,:id_3)', $this->users->id->in(array(1,2,3))->__toString());
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
        
        $this->assertEquals('users.id BETWEEN :id_1 AND :id_2', $this->users->id->between(1,3)->__toString());
        
        $this->assertEquals('users.id AS uid', $this->users->id->label('uid')->__toString());
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
    
    public function testSelectColumns()
    {
        $this->assertEquals(
            'SELECT * FROM users',
            select($this->users)->__toString()
        );
        $this->assertEquals(
            'SELECT users.*, addresses.* FROM users, addresses',
            select(array($this->users, $this->addresses))->__toString()
        );
        $this->assertEquals(
            'SELECT users.firstname, users.lastname FROM users',
            select(array($this->users->firstname, $this->users->lastname))->__toString()
        );
        $this->assertEquals(
            'SELECT users.firstname, addresses.email_address FROM users, addresses',
            select(array($this->users->firstname, $this->addresses->email_address))->__toString()
        );
    }
    
    public function testSelectDistinct()
    {
        $this->assertEquals(
            'SELECT DISTINCT users.lastname FROM users',
            select(array($this->users->lastname))->distinct()->__toString()
        );
    }
    
    public function testTableClauseSelect()
    {
        $this->assertEquals(
            'SELECT * FROM users',
            $this->users->select()->__toString()
        );
    }
    
    public function testAlias()
    {
        $this->assertEquals('users AS u', $this->users->alias('u')->__toString());
        
        $u = $this->users->alias('u');
        $this->assertEquals(
            'SELECT u.firstname, u.lastname FROM users AS u',
            select(array($u->firstname, $u->lastname))->__toString()
        );
        
        $a1 = $this->addresses->alias('a1');
        $a2 = $this->addresses->alias('a2');
        $select = select(array($this->users), and_(
            $this->users->id->eq($a1->user_id),
            $this->users->id->eq($a2->user_id),
            $a1->email_address->eq('john@gmail.com'),
            $a2->email_address->eq('john@yahoo.com')
        ));
        $this->assertEquals(
            'SELECT * FROM users WHERE users.id = a1.user_id AND users.id = a2.user_id AND a1.email_address = :email_address_1 AND a2.email_address = :email_address_2',
            $select->__toString()
        );
    }
    
    public function testSelectWithJoins()
    {
        $this->assertEquals(
            'SELECT * FROM users JOIN addresses ON users.id = addresses.user_id',
            $this->users->select()->from($this->users->join($this->addresses))->__toString()
        );
    }
    
    public function testWhere()
    {
        $this->assertEquals(
            'SELECT * FROM users WHERE users.id = :id_1',
            $this->users->select()->where($this->users->id->eq(1))->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM users WHERE users.firstname LIKE :firstname_1 AND users.lastname LIKE :lastname_1',
            $this->users->select()->where($this->users->firstname->like('John'), $this->users->lastname->like('Doe'))->__toString()
        );
    }
    
    public function testOrderBy()
    {
        $this->assertEquals(
            'SELECT * FROM users ORDER BY users.id',
            $this->users->select()->orderBy($this->users->id)->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM users ORDER BY users.id ASC,users.firstname DESC',
            $this->users->select()->orderBy($this->users->id->asc())->orderBy($this->users->firstname->desc())->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM users ORDER BY users.id ASC,users.firstname DESC',
            $this->users->select()->orderBy($this->users->id->asc(), $this->users->firstname->desc())->__toString()
        );
    }
    
    public function testOffsetAndLimit()
    {
        $this->assertEquals(
            'SELECT * FROM users LIMIT 10',
            $this->users->select()->limit(10)->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM users LIMIT -1 OFFSET 10',
            $this->users->select()->offset(10)->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM users LIMIT 10 OFFSET 10',
            $this->users->select()->limit(10)->offset(10)->__toString()
        );
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
        $this->setExpectedException('Stato\Orm\UnknownColumn');
        $this->users->insert()->values(array('foo' => 'bar'))->__toString();
    }
    
    public function testUpdate()
    {
        $this->assertEquals(
            'UPDATE users SET id = :id, firstname = :firstname, lastname = :lastname',
            $this->users->update()->__toString()
        );
        $this->assertEquals(
            'UPDATE users SET firstname = :firstname',
            $this->users->update()->values(array('firstname' => 'Jack'))->__toString()
        );
        $this->assertEquals(
            'UPDATE users SET lastname = :lastname WHERE users.lastname LIKE :lastname_1',
            $this->users->update()->values(array('lastname' => 'DOE'))->where($this->users->lastname->like('Doe'))->__toString()
        );
    }
    
    public function testDelete()
    {
        $this->assertEquals(
            'DELETE FROM users',
            $this->users->delete()->__toString()
        );
        $this->assertEquals(
            'DELETE FROM users WHERE users.lastname LIKE :lastname_1',
            $this->users->delete()->where($this->users->lastname->like('Doe'))->__toString()
        );
    }
}