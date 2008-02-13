<?php

class RolesController extends ApplicationController
{
    public function index()
    {
        
    }
    
    public function show()
    {
        $this->role = Role::$objects->get($this->params['id']);
        
        $all_actions = array();
        foreach ($this->role->permissions->all() as $perm)
            $all_actions[$perm->controller][] = $perm;
            
        $this->all_actions = $all_actions;
    }
    
    public function create()
    {
        $this->role = new Role();
        
        $all_actions = array();
        foreach (Permission::$objects->all() as $perm)
            $all_actions[$perm->controller][] = $perm;
            
        $this->all_actions = $all_actions;
        
    }
}

?>