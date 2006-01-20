<?php

class StaticX
{
    public static $fields = 1;
    
    public static function find()
    {
        return self::$fields;
    }
}

class Dummy extends StaticX
{
    public static function init()
    {
        self::$fields = 3;
    }
}

class StaticTest extends UnitTestCase
{
    function testInheritance()
    {
        Dummy::init();
        $this->assertEqual(3, Dummy::find());
    }
}

?>
