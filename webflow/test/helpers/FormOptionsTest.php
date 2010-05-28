<?php

require_once dirname(__FILE__) . '/../../../test/TestsHelper.php';

class MockRole extends MockRecord
{
    protected $attributes = array('flag', 'lib');
}

class MockUser extends MockRecord
{
    protected $attributes = array('name', 'service', 'role');
}

class FormOptionsTest extends StatoTestCase
{
    public function test_options_for_select()
    {
        $this->assertDomEquals(
            options_for_select(array('PHP', 'Apache', 'MySQL')),
            '<option value="PHP">PHP</option>
            <option value="Apache">Apache</option>
            <option value="MySQL">MySQL</option>'
        );
        $this->assertDomEquals(
            options_for_select(array('PHP', 'Apache', 'MySQL'), 'Apache'),
            '<option value="PHP">PHP</option>
            <option value="Apache" selected="selected">Apache</option>
            <option value="MySQL">MySQL</option>'
        );
        $this->assertDomEquals(
            options_for_select(array('PHP', 'Apache', 'MySQL'), array('PHP', 'Apache')),
            '<option value="PHP" selected="selected">PHP</option>
            <option value="Apache" selected="selected">Apache</option>
            <option value="MySQL">MySQL</option>'
        );
        $this->assertDomEquals(
            options_for_select(array('PHP', '<XML>'), '<XML>'),
            '<option value="PHP">PHP</option>
            <option value="&lt;XML&gt;" selected="selected">&lt;XML&gt;</option>'
        );
    }
    
    public function test_options_for_select_with_associative_array()
    {
        $this->assertDomEquals(
            options_for_select(array('Margharita'=>'7€', 'Calzone'=>'9€', 'Napolitaine'=>'8€')),
            '<option value="7€">Margharita</option>
            <option value="9€">Calzone</option>
            <option value="8€">Napolitaine</option>'
        );
        $this->assertDomEquals(
            options_for_select(array('Margharita'=>'7€', 'Calzone'=>'9€', 'Napolitaine'=>'8€'), '9€'),
            '<option value="7€">Margharita</option>
            <option value="9€" selected="selected">Calzone</option>
            <option value="8€">Napolitaine</option>'
        );
        $this->assertDomEquals(
            options_for_select(array('Margharita'=>'7€', 'Calzone'=>'9€', 'Napolitaine'=>'8€'), array('9€', '8€')),
            '<option value="7€">Margharita</option>
            <option value="9€" selected="selected">Calzone</option>
            <option value="8€" selected="selected">Napolitaine</option>'
        );
    }
    
    public function test_options_from_collection()
    {
        $roles = array(
            new MockRole('root', 'SuperAdmin'),
            new MockRole('admin', 'Administrator'),
            new MockRole('user', 'SimpleUser')
        );
        $this->assertDomEquals(
            options_from_collection_for_select($roles, 'flag', 'lib'),
            '<option value="root">SuperAdmin</option>
            <option value="admin">Administrator</option>
            <option value="user">SimpleUser</option>'
        );
        $this->assertDomEquals(
            options_from_collection_for_select($roles, 'flag', 'lib', 'root'),
            '<option value="root" selected="selected">SuperAdmin</option>
            <option value="admin">Administrator</option>
            <option value="user">SimpleUser</option>'
        );
        $this->assertDomEquals(
            options_from_collection_for_select($roles, 'flag', 'lib', array('root', 'user')),
            '<option value="root" selected="selected">SuperAdmin</option>
            <option value="admin">Administrator</option>
            <option value="user" selected="selected">SimpleUser</option>'
        );
    }
    
    public function test_select()
    {
        $services = array('Marketing', 'IT', 'Commercial');
        $user = new MockUser('John Doe');
        $this->assertDomEquals(
            select('user', 'service', $user, $services),
            '<select id="user_service" name="user[service]">
            <option value="Marketing">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>'
        );
        $user = new MockUser('Jane Doe', 'Marketing');
        $this->assertDomEquals(
            select('user', 'service', $user, $services),
            '<select id="user_service" name="user[service]">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>'
        );
        $services = array('Marketing'=>1, 'IT'=>2, 'Commercial'=>3);
        $user = new MockUser('Jane Doe', 1);
        $this->assertDomEquals(
            select('user', 'service', $user, $services),
            '<select id="user_service" name="user[service]">
            <option value="1" selected="selected">Marketing</option>
            <option value="2">IT</option>
            <option value="3">Commercial</option>
            </select>'
        );
        $this->assertDomEquals(
            select('user', 'service', $user, $services, array('include_blank' => true)),
            '<select id="user_service" name="user[service]">
            <option value=""></option>
            <option value="1" selected="selected">Marketing</option>
            <option value="2">IT</option>
            <option value="3">Commercial</option>
            </select>'
        );
        $user = new MockUser('Jane Doe');
        $this->assertDomEquals(
            select('user', 'service', $user, $services, array('prompt' => 'Please select')),
            '<select id="user_service" name="user[service]">
            <option value="">Please select</option>
            <option value="1">Marketing</option>
            <option value="2">IT</option>
            <option value="3">Commercial</option>
            </select>'
        );
        $user = new MockUser('Jane Doe', 2);
        $this->assertDomEquals(
            select('user', 'service', $user, $services, array('prompt' => 'Please select')),
            '<select id="user_service" name="user[service]">
            <option value="1">Marketing</option>
            <option value="2" selected="selected">IT</option>
            <option value="3">Commercial</option>
            </select>'
        );
    }
    
    public function test_collection_select()
    {
        $roles = array(
            new MockRole('root', 'SuperAdmin'),
            new MockRole('admin', 'Administrator'),
            new MockRole('user', 'SimpleUser')
        );
        $user = new MockUser('John Doe', 2);
        $this->assertDomEquals(
            collection_select('user', 'role', $user, $roles, 'flag', 'lib'),
            '<select id="user_role" name="user[role]">
            <option value="root">SuperAdmin</option>
            <option value="admin">Administrator</option>
            <option value="user">SimpleUser</option>
            </select>'
        );
        $user = new MockUser('John Doe', 2, 'root');
        $this->assertDomEquals(
            collection_select('user', 'role', $user, $roles, 'flag', 'lib'),
            '<select id="user_role" name="user[role]">
            <option value="root" selected="selected">SuperAdmin</option>
            <option value="admin">Administrator</option>
            <option value="user">SimpleUser</option>
            </select>'
        );
        $user = new MockUser('John Doe', 2);
        $this->assertDomEquals(
            collection_select('user', 'role', $user, $roles, 'flag', 'lib', array('include_blank' => true)),
            '<select id="user_role" name="user[role]">
            <option value=""></option>
            <option value="root">SuperAdmin</option>
            <option value="admin">Administrator</option>
            <option value="user">SimpleUser</option>
            </select>'
        );
        $user = new MockUser('John Doe', 2);
        $this->assertDomEquals(
            collection_select('user', 'role', $user, $roles, 'flag', 'lib', array('prompt' => 'Please select')),
            '<select id="user_role" name="user[role]">
            <option value="">Please select</option>
            <option value="root">SuperAdmin</option>
            <option value="admin">Administrator</option>
            <option value="user">SimpleUser</option>
            </select>'
        );
        $user = new MockUser('John Doe', 2, 'user');
        $this->assertDomEquals(
            collection_select('user', 'role', $user, $roles, 'flag', 'lib', array('prompt' => 'Please select')),
            '<select id="user_role" name="user[role]">
            <option value="root">SuperAdmin</option>
            <option value="admin">Administrator</option>
            <option value="user" selected="selected">SimpleUser</option>
            </select>'
        );
    }
}
