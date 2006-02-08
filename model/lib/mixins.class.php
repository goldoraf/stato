<?php

// Works perfectly with Apache 2.0.54 / PHP 5.0.4 / runkit 0.4 (XAMPP 1.4.15)
// Crashes Apache with Apache 2.0.55 / PHP 5.0.5 / runkit 0.7 (XAMPP 1.5.0)
class SMixins
{
    private static $mixins = array();
    
    public static function aggregate($destClass, $sourceClass)
    {
        $destClass = strtolower($destClass);
        $sourceClass = strtolower($sourceClass);
        
        if (!isset(self::$mixins[$destClass]) || !in_array($sourceClass, self::$mixins[$destClass]))
        {
            if (!function_exists('runkit_method_copy'))
                throw new Exception('Warning : runkit extension must be installed to support mixins features.');
                
            $reflection = new ReflectionClass($sourceClass);
            $sourceMethods = $reflection->getMethods();
            
            $destReflection = new ReflectionClass($destClass);
            // à virer en 5.1
            $destMethods = array();
            foreach($destReflection->getMethods() as $method) $destMethods[] = $method->name;
            
            foreach($sourceMethods as $method)
            {
                //if ($destReflection->hasMethod($method->name)) pas avant PHP 5.1
                if (!in_array($method->name, $destMethods))
                {
                    runkit_method_copy($destClass, $method->name, $sourceClass);
                }
                else
                {
                    runkit_method_remove($destClass, $method->name);
                    runkit_method_copy($destClass, $method->name, $sourceClass);
                }
            } 
            self::$mixins[$destClass][] = $sourceClass;
        }
    }
}

?>
