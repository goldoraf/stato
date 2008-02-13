<?php

class Role extends SActiveRecord
{
    public static $objects;
    public static $table_name = null;
    public static $relationships = array('users' => 'many_to_many', 'permissions' => 'many_to_many');
}

Role::$table_name = AclEngine::config('roles_table');

?>
