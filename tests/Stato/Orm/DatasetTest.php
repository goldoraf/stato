<?php

namespace Stato\Orm;

require_once __DIR__ . '/../TestsHelper.php';

class DatasetTest extends TestCase
{
    protected $fixtures = array('users');
    
    public function testBasicSelect()
    {
        $this->assertEquals('SELECT * FROM users', $this->compile($this->db->from('users'))->__toString());
        
        $stmt = $this->compile($this->db->from('users')->filter(array('fullname' => 'John Doe')));
        $this->assertEquals('SELECT * FROM users WHERE users.fullname = :fullname_1', $stmt->string);
        $this->assertEquals(array(':fullname_1' => 'John Doe'), $stmt->params);
        
        $stmt = $this->compile($this->db->from('users')->filter(array('login' => 'jdoe', 'password' => 'test')));
        $this->assertEquals('SELECT * FROM users WHERE users.login = :login_1 AND users.password = :password_1', $stmt->string);
        $this->assertEquals(array(':login_1' => 'jdoe', ':password_1' => 'test'), $stmt->params);
        
        $stmt = $this->compile($this->db->from('users')->filter(array('id' => array(1, 2))));
        $this->assertEquals('SELECT * FROM users WHERE users.id IN (:id_1,:id_2)', $stmt->string);
        $this->assertEquals(array(':id_1' => 1, ':id_2' => 2), $stmt->params);
    }
    
    protected function compile($stmt)
    {
        $compiler = new Compiler();
        return $compiler->compile($stmt);
    }
}