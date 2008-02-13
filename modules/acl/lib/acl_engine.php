<?php

require('acl_engine/acl_enabled_controller.php');
require('acl_engine/acl_helper.php');

class AclEngine
{
    private static $config = array();
    
    public static function start()
    {
        self::set_config('use_permission_system', true);
        
        self::set_config('salt', 'your salt value');
        self::set_config('app_name', 'Your Site');
        
        self::set_config('roles_table', 'roles');
        self::set_config('permissions_table', 'permissions');
        self::set_config('users_table', 'users');
        
        self::set_config('roles_users_table', self::config('roles_table').'_'.self::config('users_table'));
        self::set_config('permissions_roles_table', self::config('permissions_table').'_'.self::config('roles_table'));
        self::set_config('permissions_users_table', self::config('permissions_table').'_'.self::config('users_table'));
        
        self::set_config('guest_role_name', 'Guest');
        self::set_config('user_role_name', 'User');
        
        self::set_config('admin_role_name', 'Admin');
        self::set_config('admin_login', 'admin');
        self::set_config('admin_password', 'testing');
        self::set_config('admin_email', 'admin@yoursite.com');
        
        self::set_config('use_email_notification', true);
        self::set_config('login_page', array('controller' => 'user', 'action' => 'login'));
        self::set_config('confirm_account', true);
        self::set_config('security_token_life_hours', 4);
        self::set_config('email_from', 'support@yoursite.com');
        self::set_config('changeable_fields', array('firstname', 'lastname', 'email', 'login'));
        self::set_config('delayed_delete', true);
        self::set_config('delayed_delete_days', 1);
    }
    
    public static function authenticate($login, $password)
    {
        try {
            $user = User::$objects->get('login = ?', 'verified = 1', 'deleted = 0', array($login));
            return User::$objects->get('login = ?', 'salted_password = ?', 'verified = 1', 'deleted = 0',
                    array($login, self::salted_password($user->salt, self::hashed($password))));
        } catch (SRecordNotFound $e) {
            return false;
        }
    }
    
    public static function authenticate_by_token($user_id, $token)
    {
        try {
            $user = User::$objects->get('id = ?', 'security_token = ?', array($user_id, $token));
            if ($user->is_token_expired()) return false;
            return $user;
        } catch (SRecordNotFound $e) {
            return false;
        }
    }
    
    public static function is_authorized($user, $controller, $action = 'index')
    {
        if ($user->is_superuser()) return true;
        
        $sql =   'SELECT DISTINCT '.self::config('permissions_table').'.*'
                .' FROM '.self::config('permissions_table').', '.self::config('roles_table').', '
                        .self::config('permissions_roles_table').', '.self::config('roles_users_table').', '
                        .self::config('users_table')
                .' WHERE '.self::config('users_table').'.id = :user_id'
                .' AND '.self::config('users_table').'.id = '.self::config('roles_users_table').'.user_id'
                .' AND '.self::config('roles_users_table').'.role_id = '.self::config('roles_table').'.id'
                .' AND '.self::config('roles_table').'.id = '.self::config('permissions_roles_table').'.role_id'
                .' AND '.self::config('permissions_roles_table').'.permission_id = '.self::config('permissions_table').'.id'
                .' AND '.self::config('permissions_table').'.controller = :controller'
                .' AND '.self::config('permissions_table').'.action = :action';
        
        $conn = User::connection();
        $result = $conn->select_all(
            $conn->sanitize_sql($sql, array(
                ':user_id' => $user->id, ':controller' => $controller, ':action' => $action))
        );
        return !empty($result);
    }
    
    public static function is_guest_user_authorized($controller, $action = 'index')
    {
        $sql =   'SELECT DISTINCT '.self::config('permissions_table').'.*'
                .' FROM '.self::config('permissions_table').', '.self::config('roles_table').', '
                        .self::config('permissions_roles_table')
                .' WHERE '.self::config('roles_table').'.name = :role'
                .' AND '.self::config('roles_table').'.id = '.self::config('permissions_roles_table').'.role_id'
                .' AND '.self::config('permissions_roles_table').'.permission_id = '.self::config('permissions_table').'.id'
                .' AND '.self::config('permissions_table').'.controller = :controller'
                .' AND '.self::config('permissions_table').'.action = :action';
        
        $conn = User::connection();
        $result = $conn->select_all(
            $conn->sanitize_sql($sql, array(
                ':role' => self::config('guest_role_name', 'Guest'), ':controller' => $controller, ':action' => $action))
        );
        return !empty($result);
    }
    
    public static function synchronize_with_controllers($controllers_path = null)
    {
        require_once(STATO_APP_ROOT_PATH.'/app/controllers/application_controller.php');
        
        if ($controllers_path == null) $controllers_path = STATO_APP_ROOT_PATH.'/app/controllers';
        $controllers = self::find_controllers($controllers_path);
        foreach ($controllers as $controller => $options)
        {
            require_once(STATO_APP_ROOT_PATH.'/app/controllers/'.$controller.'_controller.php');
            $ref = new ReflectionClass(self::controller_class($controller));
            foreach ($ref->getMethods() as $method)
                if ($method->isPublic() && !$method->isConstructor()
                    && $method->getDeclaringClass()->getName() != 'SActionController')
			Permission::$objects->create(array(
			    'controller' => $controller,
			    'action' => $method->getName(),
			    'subdir' => ((isset($options['subdir'])) ? $options['subdir'] : null)
			));
        }
    }
    
    public static function hashed($str)
    {
        if (self::config('salt') == null)
            throw new Exception('You must define a salt value in the configuration for the ACL engine.');
        
        return sha1(self::config('salt').$str);
    }
    
    public static function random_password($length = 10, $allowed_chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789')
    {
        srand((double)microtime() * 1000000);
        $password = '';
        for ($i=0; $i<$length; $i++)
            $password.= $allowed_chars{(rand() % 33)};
            
        return $password;
    }
    
    public static function salt()
    {
        return self::hashed(substr(md5(uniqid(rand(), true)), 0, 12));
    }
    
    public static function salted_password($salt, $hashed_password)
    {
        return self::hashed($salt.$hashed_password);
    }
    
    public static function set_config($key, $value)
    {
        if (!isset(self::$config[$key])) self::$config[$key] = $value;
    }
    
    public static function config($key)
    {
        if (isset(self::$config[$key])) return self::$config[$key];
    }
    
    private static function find_controllers($path, $include_subdirs = true)
    {
        $dir = new DirectoryIterator($path);
        $controllers = array();
        
        foreach ($dir as $file)
        {
            if (!$file->isDot() && $file->getFilename() != '.svn')
            {
                if ($file->isDir() && $include_subdirs)
                    foreach (self::find_controllers($file->getPathname(), false) as $c => $o)
                        $controllers[$file->getFilename().'/'.$c] = array('subdir' => $file->getFilename());
                elseif ($file->getFilename() != 'application_controller.php')
                    $controllers[str_replace('_controller.php', '', $file->getFilename())] = array();
            }
        }
        return $controllers;
    }
    
    private static function controller_class($controller)
    {
        if (strpos($controller, '/') === false) $controller_name = $controller; 
    	else list( , $controller_name) = explode('/', $controller);
         
    	return SInflection::camelize($controller_name).'Controller';
    }
}

?>
