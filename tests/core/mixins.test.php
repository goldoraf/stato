<?php

class Base
{
    public function __construct()
    {
        SMixins::aggregate(__CLASS__, 'Mixin');
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

class SMixinsTest extends UnitTestCase
{
    function testAggregation()
    {
        $base = new Base();
        $this->assertEqual(1, $base->test1());
        $this->assertEqual(2, $base->test2());
    }
}

?>
