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
}