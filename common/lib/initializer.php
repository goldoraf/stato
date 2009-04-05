<?php

class SInitializer
{
    private static $config;
    
    public static function boot()
    {
        $config = new SConfiguration();
        
        require_once(STATO_CORE_PATH.'/common/common.php');
        require_once(STATO_CORE_PATH.'/webflow/webflow.php');
        require_once(STATO_CORE_PATH.'/orm/orm.php');
        
        include(STATO_APP_ROOT_PATH.'/conf/environment.php');
        
        self::run($config);
    }
    
    public static function run(SConfiguration $config)
    {
        self::$config = $config;
        
        self::load_environment($config);
        self::initialize_logger();
        self::initialize_main_classes_settings();
        self::initialize_database_settings();
        self::initialize_optional_packages();
    }
    
    public static function load_environment(SConfiguration $config)
    {
        include($config->environment_path());
    }
    
    private static function initialize_logger()
    {
        SLogger::initialize(self::$config->log_path);
        $logger = SLogger::get_instance();
        $logger->formatter = new SBasicFormatter();
    }
    
    private static function initialize_main_classes_settings()
    {
        foreach (self::$config->main_classes() as $ns => $class)
        {
            if (!class_exists($class, false)) continue;
            
            $ref = new ReflectionClass($class);
            foreach (self::$config->$ns->keys() as $prop)
                if ($ref->hasProperty($prop)) 
                    $ref->setStaticPropertyValue($prop, self::$config->$ns->$prop);
        }
    }
    
    private static function initialize_database_settings()
    {
        SActiveRecord::$configurations = self::$config->database_configuration();
    }
    
    private static function initialize_optional_packages()
    {
        if (!self::$config->use_i18n)
        {
            function __($key, $options = array()) {
                return $key;
            }
        }
    }
    
    private static function is_cli_env()
    {
        return defined('STDIN') && defined('STDOUT') && defined('STDERR');
    }
}

class SConfiguration
{
    public $action_controller;
    public $action_view;
    public $active_record;
    
    public $log_path;
    public $database_config_file;
    public $use_i18n;
    public $use_mailer;
    
    private $main_classes = array
    (
        'controller' => 'SActionController',
        'model'      => 'SActiveRecord',
        'view'       => 'SActionView',
    );
    
    private $namespaces = array
    (
        'controller' => 'action_controller',
        'model'      => 'active_record',
        'view'       => 'action_view',
    );
    
    public function __construct()
    {
        $this->action_controller = new SOptionsHash();
        $this->action_view       = new SOptionsHash();
        $this->active_record     = new SOptionsHash();
        
        $this->log_path = STATO_APP_ROOT_PATH.'/log/'.STATO_ENV.'.log';
        $this->database_config_file = STATO_APP_ROOT_PATH.'/conf/database.php';
        $this->use_i18n = false;
        $this->use_mailer = false;
    }
    
    public function environment_path()
    {
        return STATO_APP_ROOT_PATH.'/conf/environments/'.STATO_ENV.'.php';
    }
    
    public function database_configuration()
    {
        return include($this->database_config_file);
    }
    
    public function main_classes()
    {
        $classes = array();
        foreach ($this->namespaces as $k => $ns) 
            $classes[$ns] = $this->main_classes[$k];
        return $classes;
    }
}

class SOptionsHash
{
    private $values = array();
    
    public function __get($name)
    {
        if (!isset($this->values[$name])) return null;
        return $this->values[$name];
    }
    
    public function __set($name, $value)
    {
        $this->values[$name] = $value;
        return true;
    }
    
    public function keys()
    {
        return array_keys($this->values);
    }
    
    public function is_empty()
    {
        return count($this->values) == 0;
    }
}

?>
