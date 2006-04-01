<?php

class DomAssertEqualTest extends UnitTestCase
{
    function testDomAssertEqual()
    {
        $this->assertTrue($this->assertDomEqual('<fake />', '<fake />'));
        $this->assertTrue($this->assertDomEqual('<fake />', '<fake/>'));
        $this->assertTrue($this->assertDomEqual("<fake />\n", '<fake />'));
        $this->assertFalse($this->assertDomEqual('<fake />', '<nuke />'));
        $this->assertTrue($this->assertDomEqual('<fake></fake>', '<fake />'));
        
        $this->assertTrue($this->assertDomEqual(
            '<fake key1="value1" key2="value2" />',
            '<fake key1="value1" key2="value2" />'
        ));
        $this->assertTrue($this->assertDomEqual(
            '<fake key1="value1" key2="value2" />',
            '<fake key2="value2" key1="value1" />'
        ));
        $this->assertFalse($this->assertDomEqual(
            '<fake key1="value1" key2="value2" />',
            '<fake key="value1" key2="value2" />'
        ));
        $this->assertFalse($this->assertDomEqual(
            '<fake key1="value1" key2="value2" />',
            '<fake key="value" key2="value2" />'
        ));
        $this->assertTrue($this->assertDomEqual(
            '<fake key1="value1" key2="value2" /><test />',
            '<fake key1="value1" key2="value2" /><test />'
        ));
        $this->assertFalse($this->assertDomEqual(
            '<fake key1="value1" key2="value2" /><test />',
            '<test /><fake key2="value2" key1="value1" />'
        ));
        $this->assertTrue($this->assertDomEqual(
            '<fake>hello world</fake>',
            '<fake>hello world</fake>'
        ));
        $this->assertFalse($this->assertDomEqual(
            '<fake>hello</fake>',
            '<fake>hello world</fake>'
        ));
        $this->assertTrue($this->assertDomEqual(
            '<say><hello><world>PHP</world></hello></say><fake></fake>',
            '<say><hello><world>PHP</world></hello></say><fake></fake>'
        ));
        $this->assertFalse($this->assertDomEqual(
            '<say><hello><world>PHP</world></hello></say><fake></fake>',
            '<say><world><hello>PHP</hello></world></say><fake></fake>'
        ));
    }
    
    function assertDomEqual($first, $second)
    {
        $expectation = new DomEqualExpectation($first);
        return $expectation->test($second);
    }
}

?>
