<?php

class Configuration
{
    private static $settings = array();
    private static $types    = array();
    
    public static function initialize()
    {
        self::setting('site_name', 'string', 'Mon nouveau site');
        self::setting('site_title', 'string', 'Bienvenue sur mon nouveau site');
        self::setting('site_description', 'text', 'site vraiment très intéressant');
        self::setting('limit_post_rss', 'int', 10);
        self::setting('limit_post_home_page', 'int', 5);
        self::setting('limit_post_news_page', 'int', 10);
        self::setting('webmaster_mail', 'string', 'dummy@ccip.fr');
        
        self::load_settings();
    }
    
    public static function value($name)
    {
        if (array_key_exists($name, self::$settings)) return self::$settings[$name];
        return null;
    }
    
    public static function update_value($name, $value)
    {
        if (array_key_exists($name, self::$settings))
        {
            self::$settings[$name] = self::normalize_value(self::$types[$name], $value);
            Setting::$objects->get_or_create(array('name' => $name))->update_attribute('value', $value);
        }
    }
    
    public static function load_settings()
    {
        foreach (Setting::$objects->all() as $setting)
            if (array_key_exists($setting->name, self::$settings))
                self::$settings[$setting->name] 
                    = self::normalize_value(self::$types[$name], $setting->value);
    }
    
    private static function setting($name, $type, $default_value)
    {
        self::$settings[$name] = $default_value;
        self::$types[$name]    = $type;
    }
    
    private static function normalize_value($type, $value)
    {
        switch ($type) {
            case 'bool':
                return ((integer) $value == 0) ? false : true;
            case 'int':
                return (integer) $value;
            default:
                return $value;
        }
    }
}

?>
