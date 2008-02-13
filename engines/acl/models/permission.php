<?php

class PermissionManager extends SManager
{
    public function find_by_controller_and_action($controller, $action)
    {
	return $this->get("controller = ?", "action = ?", array($controller, $action));
    }
}

class Permission extends SActiveRecord
{
    public static $objects;
    public static $table_name = null;
    public static $relationships = array('roles' => 'many_to_many');
}

Permission::$table_name = AclEngine::config('permissions_table');

?>
