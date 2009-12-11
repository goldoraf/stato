<?php

namespace Stato\Orm;

require_once __DIR__ . '/../TestsHelper.php';

class DatasetTest extends TestCase
{
    protected $fixtures = array('users');
    
    public function testBasicSql()
    {
        $this->assertEquals('SELECT * FROM users', $this->db->from('users')->__toString());
    }
    
    public function testFilterBy()
    {
        $this->assertEquals(
            'SELECT * FROM users WHERE users.fullname = :fullname_1',
            $this->db->from('users')->filterBy(array('fullname' => 'John Doe'))->__toString()
        );
        
        $this->assertEquals(
            'SELECT * FROM users WHERE users.login = :login_1 AND users.password = :password_1',
            $this->db->from('users')->filterBy(array('login' => 'jdoe', 'password' => 'test'))->__toString()
        );
        
        $this->assertEquals(
            'SELECT * FROM users WHERE users.id IN (:id_1,:id_2)',
            $this->db->from('users')->filterBy(array('id' => array(1, 2)))->__toString()
        );
    }
    
    public function testFilter()
    {
        $u = $this->db->getTable('users');
        $this->assertEquals(
            'SELECT * FROM users WHERE users.fullname = :fullname_1',
            $this->db->from('users')->filter($u->fullname->eq('John Doe'))->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM users WHERE users.login = :login_1 AND users.password = :password_1',
            $this->db->from('users')->filter($u->login->eq('jdoe'), $u->password->eq('test'))->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM users WHERE users.login = :login_1 AND users.password = :password_1',
            $this->db->from('users')->filter(and_($u->login->eq('jdoe'), $u->password->eq('test')))->__toString()
        );
    }
    
    public function testFilterWithClosures()
    {
        $this->assertEquals(
            'SELECT * FROM users WHERE users.fullname = :fullname_1',
            $this->db->from('users')->filter(function($u) { return $u->fullname->eq('John Doe'); })->__toString()
        );
    }
    
    public function testExcludeBy()
    {
        $this->assertEquals(
            'SELECT * FROM users WHERE users.fullname != :fullname_1',
            $this->db->from('users')->excludeBy(array('fullname' => 'John Doe'))->__toString()
        );
        
        $this->assertEquals(
            'SELECT * FROM users WHERE NOT (users.login = :login_1 AND users.password = :password_1)',
            $this->db->from('users')->excludeBy(array('login' => 'jdoe', 'password' => 'test'))->__toString()
        );
        
        $this->assertEquals(
            'SELECT * FROM users WHERE users.id NOT IN (:id_1,:id_2)',
            $this->db->from('users')->excludeBy(array('id' => array(1, 2)))->__toString()
        );
    }
    
    public function testExclude()
    {
        $u = $this->db->getTable('users');
        $this->assertEquals(
            'SELECT * FROM users WHERE users.fullname != :fullname_1',
            $this->db->from('users')->exclude($u->fullname->eq('John Doe'))->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM users WHERE NOT (users.login = :login_1 AND users.password = :password_1)',
            $this->db->from('users')->exclude($u->login->eq('jdoe'), $u->password->eq('test'))->__toString()
        );
        $this->assertEquals(
            'SELECT * FROM users WHERE NOT (users.login = :login_1 AND users.password = :password_1)',
            $this->db->from('users')->exclude(and_($u->login->eq('jdoe'), $u->password->eq('test')))->__toString()
        );
    }
    
    public function testLimit()
    {
        $users = $this->db->from('users')->filter(function($u) { return $u->fullname->like('%Doe'); })->limit(1)->toArray();
        $this->assertEquals(1, count($users));
        $this->assertEquals('John Doe', $users[0]['fullname']);
        $users = $this->db->from('users')->filter(function($u) { return $u->fullname->like('%Doe'); })->limit(1, 1)->toArray();
        $this->assertEquals(1, count($users));
        $this->assertEquals('Jane Doe', $users[0]['fullname']);
    }
    
    public function testOrderBy()
    {
        $u = $this->db->getTable('users');
        $users = $this->db->from('users')->orderBy($u->id->desc())->toArray();
        $this->assertEquals('John Doe', $users[1]['fullname']);
        $this->assertEquals('Jane Doe', $users[0]['fullname']);
    }
    
    public function testOrderByString()
    {
        $users = $this->db->from('users')->orderBy('-id')->toArray();
        $this->assertEquals('John Doe', $users[1]['fullname']);
        $this->assertEquals('Jane Doe', $users[0]['fullname']);
    }
    
    public function testGenerativeSelects()
    {
        $q1 = $this->db->from('users')->filter(function($u) { return $u->password->eq('test'); });
        $q2 = $q1->filter(function($u) { return $u->login->ne('jdoe'); });
        $this->assertNotEquals($q2, $q1);
    }
    
    public function testGet()
    {
        $this->assertEquals(array('id' => '1', 'fullname' => 'John Doe', 'login' => 'jdoe', 'password' => 'john'),
            $this->db->from('users')->get(1));
    }
    
    public function testRecordNotFound()
    {
        $this->setExpectedException('Stato\Orm\RecordNotFound');
        $user = $this->db->from('users')->get(99);
    }
    
    public function testInsert()
    {
        $res = $this->db->from('users')->insert(array('fullname' => 'Foo Bar', 'login' => 'foo', 'password' => 'bar'));
        $this->assertEquals(3, $res->lastInsertId());
        $this->assertEquals(1, $res->rowCount());
    }
}