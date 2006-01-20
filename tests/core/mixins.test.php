<?php

class Base
{
    public function __construct()
    {
        Mixins::aggregate(__CLASS__, 'Mixin');
    }
    
    public function test1()
    {
        return 1;
    }
}

class Mixin
{
    public function test2()
    {
        return 2;
    }
}

class MixinsTest extends UnitTestCase
{
    function testAggregation()
    {
        //runkit_method_add('Base', 'test2', '', 'return 2;', RUNKIT_ACC_PUBLIC);
        runkit_method_copy('Base', 'test2', 'Mixin', 'test2');
        $base = new Base();
        $this->assertEqual(1, $base->test1());
        $this->assertEqual(2, $base->test2());
    }
}

?>
