<?php

namespace Stato\Model;

require_once __DIR__ . '/TestsHelper.php';

use User;
use DateTime;

class DatasetTest extends TestCase
{
    public function setup()
    {
        parent::setup();
        User::create(array('fullname' => 'John Doe', 'login' => 'jdoe', 'password' => 'foo', 'country' => 'United States'));
        User::create(array('fullname' => 'Jane Doe', 'login' => 'jane', 'password' => 'bar', 'country' => 'United States'));
        User::create(array('fullname' => 'Rasmus L', 'login' => 'rasm', 'password' => 'php', 'country' => 'Denmark'));
    }
    
    public function testGet()
    {
        $user = User::get(1);
        $this->assertEquals('John Doe', $user->fullname);
    }
    
    public function testRecordNotFound()
    {
        $this->setExpectedException('\Stato\Model\RecordNotFound');
        $user = User::get(9999);
    }
    
    public function testAll()
    {
        $this->assertEquals(3, count(User::all()));
    }
    
    public function testFilterWithArrayArgs()
    {
        $users = User::filter(array('country' => 'United States'))->all();
        $this->assertEquals(2, count($users));
        $this->assertEquals('jdoe', $users[0]->login);
        $this->assertEquals('jane', $users[1]->login);
    }
    
    public function testFilterWithConditions()
    {
        $u = User::getMetaclass();
        $users = User::filter($u->login->eq('jdoe'), $u->password->eq('foo'))->all();
        $this->assertEquals(1, count($users));
        $this->assertEquals('jdoe', $users[0]->login);
    }
    
    public function testFilterWithClosures()
    {
        $users = User::filter(function($u) { return $u->login->eq('jdoe'); })->all();
        $this->assertEquals(1, count($users));
        $this->assertEquals('jdoe', $users[0]->login);
    }
    
    public function testExcludeWithArrayArgs()
    {
        $users = User::exclude(array('country' => 'United States'))->all();
        $this->assertEquals(1, count($users));
        $this->assertEquals('rasm', $users[0]->login);
    }
    
    public function testExcludeWithConditions()
    {
        $u = User::getMetaclass();
        $users = User::exclude($u->country->eq('United States'))->all();
        $this->assertEquals(1, count($users));
        $this->assertEquals('rasm', $users[0]->login);
    }
    
    public function testExcludeWithClosures()
    {
        $users = User::exclude(function($u) { return $u->country->eq('United States'); })->all();
        $this->assertEquals(1, count($users));
        $this->assertEquals('rasm', $users[0]->login);
    }
    
    public function testIterable()
    {
        $users = array();
        foreach (User::filter(array('country' => 'United States')) as $u) $users[] = $u;
        $this->assertEquals(2, count($users));
        $this->assertEquals('jdoe', $users[0]->login);
        $this->assertEquals('jane', $users[1]->login);
    }
    
    public function testLimit()
    {
        $users = User::filter(array('country' => 'United States'))->limit(1)->all();
        $this->assertEquals(1, count($users));
        $this->assertEquals('jdoe', $users[0]->login);
    }
    
    public function testLimitAndOffset()
    {
        $users = User::filter(array('country' => 'United States'))->limit(1, 1)->all();
        $this->assertEquals(1, count($users));
        $this->assertEquals('jane', $users[0]->login);
    }
    
    public function testOrderBy()
    {
        $u = User::getMetaclass();
        $users = User::filter($u->country->eq('United States'))->orderBy($u->id->desc())->all();
        $this->assertEquals(array(2, 1), array($users[0]->id, $users[1]->id));
    }
    
    public function testOrderByString()
    {
        $u = User::getMetaclass();
        $users = User::filter($u->country->eq('United States'))->orderBy('-id')->all();
        $this->assertEquals(array(2, 1), array($users[0]->id, $users[1]->id));
    }
    
    public function testGenerativeQueries()
    {
        $u = User::getMetaclass();
        $q1 = User::filter($u->country->eq('United States'));
        $q2 = $q1->filter($u->login->ne('jdoe'));
        $this->assertNotSame($q1, $q2);
        $this->assertEquals(2, count($q1->all()));
        $this->assertEquals(1, count($q2->all()));
    }
}