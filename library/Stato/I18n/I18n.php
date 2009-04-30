<?php

namespace
{
    function __($key, $values = array())
    {
        return Stato\I18n\I18n::translate($key, $values);
    }

    function _f($key, $values = array())
    {
        return Stato\I18n\I18n::translatef($key, $values);
    }

    function _p($key, $count = 0)
    {
        return Stato\I18n\I18n::translateAndPluralize($key, $count);
    }
}

namespace Stato\I18n
{
    class Exception extends \Exception {}

    /**
     * I18n and localization class
     *
     * @package Stato
     * @subpackage I18n
     */
    class I18n
    {
        private static $backend;
        
        private static $locale;
        
        private static $defaultLocale = 'en';
        
        private static $dataPaths = array();
        
        public static function setBackend(Backend\Simple $backend)
        {
            self::$backend = $backend;
        }
        
        public static function getBackend()
        {
            if (!isset(self::$backend)) 
                self::setBackend(new Backend\Simple());
            
            return self::$backend;
        }
        
        public static function setDefaultLocale($locale)
        {
            self::$defaultLocale = $locale;
        }
        
        public static function getDefaultLocale()
        {
            return self::$defaultLocale;
        }
        
        public static function setLocale($locale)
        {
            self::$locale = $locale;
        }
        
        public static function getLocale()
        {
            if (!isset(self::$locale))
                return self::getDefaultLocale();
            
            return self::$locale;
        }
        
        public static function addDataPath($path)
        {
            self::$dataPaths[] = $path;
        }
        
        public static function getDataPaths()
        {
            return self::$dataPaths;
        }
        
        public static function translate($key, $values = array())
        {
            $locale = self::getLocale();
            return self::getBackend()->translate($locale, $key, $values);
        }
        
        public static function translatef($key, $values = array())
        {
            $locale = self::getLocale();
            return self::getBackend()->translatef($locale, $key, $values);
        }
        
        public static function translateAndPluralize($key, $count = 0)
        {
            $locale = self::getLocale();
            return self::getBackend()->translateAndPluralize($locale, $key, $count);
        }
    }
}
