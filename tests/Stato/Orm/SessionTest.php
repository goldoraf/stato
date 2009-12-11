<?php

namespace Stato\Orm;

use User;

require_once __DIR__ . '/../TestsHelper.php';

require_once __DIR__ . '/files/user.php';

class SessionTest extends TestCase
{
    protected $fixtures = array('users');
    
    public function setup()
    {
        parent::setup();
        $this->db->map('User', 'users');
        $this->session = new Session($this->db);
    }
    
    public function testQuery()
    {
        $this->assertThat($this->session->query('User'), $this->isInstanceOf('Stato\Orm\Dataset'));
    }
    
    public function testSaveAndFlush()
    {
        $user = new User(array('fullname' => 'James Bond', 'login' => 'james', 'password' => '007'));
        $this->session->save($user);
        $this->session->flush();
        $reloaded_user = $this->session->query('User')->filterBy(array('login' => 'james'))->first();
        $this->assertEquals(3, $reloaded_user->id);
        $this->assertSame($user, $reloaded_user);
    }
}