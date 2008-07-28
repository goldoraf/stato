<?php

class AddAclEngineTables extends SMigration
{
    public function up()
    {
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('lastname', 'string', array('limit' => 40));
        $t->add_column('firstname', 'string', array('limit' => 40));
        $t->add_column('email', 'string', array('limit' => 60, 'default' => '', 'null' => false));
        $t->add_column('login', 'string', array('limit' => 60, 'default' => '', 'null' => false));
        $t->add_column('salted_password', 'string', array('limit' => 60, 'default' => '', 'null' => false));
        $t->add_column('salt', 'string', array('limit' => 60, 'default' => '', 'null' => false));
        $t->add_column('verified', 'boolean', array('default' => false));
        $t->add_column('security_token', 'string', array('limit' => 60));
        $t->add_column('token_expiry', 'datetime');
        $t->add_column('logged_in_on', 'datetime');
        $t->add_column('created_on', 'datetime');
        $t->add_column('updated_on', 'datetime');
        $t->add_column('deleted', 'boolean', array('default' => false));
        $t->add_column('delete_after', 'datetime');
        
        $this->create_table(AclEngine::config('users_table'), $t, 'ENGINE=InnoDB');
        
        if (!AclEngine::config('use_permission_system')) return;
        
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('name', 'string', array('null' => false));
        $t->add_column('description', 'string');
        $t->add_column('omnipotent', 'boolean', array('default' => false));
        
        $this->create_table(AclEngine::config('roles_table'), $t, 'ENGINE=InnoDB');
        
        $t = new STable();
        $t->add_primary_key('id');
        $t->add_column('subdir', 'string');
        $t->add_column('controller', 'string', array('null' => false));
        $t->add_column('action', 'string', array('null' => false));
        $t->add_column('description', 'string');
        
        $this->create_table(AclEngine::config('permissions_table'), $t, 'ENGINE=InnoDB');
        
        $t = new STable();
        $t->add_column('role_id', 'integer');
        $t->add_column('user_id', 'integer');
        
        $this->create_table(AclEngine::config('roles_users_table'), $t, 'ENGINE=InnoDB');
        
        $t = new STable();
        $t->add_column('role_id', 'integer');
        $t->add_column('permission_id', 'integer');
        
        $this->create_table(AclEngine::config('permissions_roles_table'), $t, 'ENGINE=InnoDB');
        
        $t = new STable();
        $t->add_column('user_id', 'integer');
        $t->add_column('permission_id', 'integer');
        
        $this->create_table(AclEngine::config('permissions_users_table'), $t, 'ENGINE=InnoDB');
        
        SDependencies::require_models(array('user', 'role', 'permission'));
        
        $this->announce('Creating permissions');
        AclEngine::synchronize_with_controllers();
        
        $this->announce('Creating guest role');
        $r = new Role();
        $r->name        = AclEngine::config('guest_role_name');
        $r->description = 'Implicit role for all accessors of the site';
        $r->omnipotent  = false;
        $r->save();
        
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'login'));
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'forgot_password'));
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'signup'));
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'confirm'));
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'restore_deleted'));
        $r->save();
        
        $this->announce('Creating admin role');
        $r = new Role();
        $r->name        = AclEngine::config('admin_role_name');
        $r->description = 'The system administrator';
        $r->omnipotent  = true;
        $r->save();
        
        $this->announce('Creating user role');
        $r = new Role();
        $r->name        = AclEngine::config('user_role_name');
        $r->description = 'The default role for all logged-in users';
        $r->omnipotent  = false;
        $r->save();
        
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'logout'));
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'home'));
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'change_password'));
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'edit'));
        $r->permissions->add(Permission::$objects->find_by_controller_and_action('auth', 'delete'));
        $r->save();
        
        $this->announce('Creating admin user');
        $u = new User();
        $u->login     = AclEngine::config('admin_login');
        $u->password  = AclEngine::config('admin_password');
        $u->email     = AclEngine::config('admin_email');
        $u->verified  = true;
        $u->change_password(AclEngine::config('admin_password'));
        $u->save();
        
        $u->roles->add(Role::$objects->get("name = '".AclEngine::config('admin_role_name')."'"));
        $u->save();
    }
    
    public function down()
    {
        $this->drop_table(AclEngine::config('users_table'));
        $this->drop_table(AclEngine::config('roles_table'));
        $this->drop_table(AclEngine::config('permissions_table'));
        $this->drop_table(AclEngine::config('roles_users_table'));
        $this->drop_table(AclEngine::config('permissions_roles_table'));
        $this->drop_table(AclEngine::config('permissions_users_table'));
    }
}

?>
