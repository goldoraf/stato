<?php

require_once dirname(__FILE__) . '/../../../tests/TestsHelper.php';

require_once 'helpers/string.php';
require_once 'helpers/form.php';

class Stato_FormHelperTest extends PHPUnit_Framework_TestCase
{
	public function testTagOptions()
	{
		$this->assertEquals('foo="bar" disabled="disabled"',
			tag_options(array('foo' => 'bar', 'disabled' => true, 'reject1' => null, 'reject2' => false)));
	}
	
	public function testOptionsForSelect()
    {
        $this->assertDomEquals(
            '<option value="PHP">PHP</option>
            <option value="Apache">Apache</option>
            <option value="MySQL">MySQL</option>',
            options_for_select(array('PHP', 'Apache', 'MySQL'))
        );
        $this->assertDomEquals(
            '<option value="PHP">PHP</option>
            <option value="Apache" selected="selected">Apache</option>
            <option value="MySQL">MySQL</option>',
            options_for_select(array('PHP', 'Apache', 'MySQL'), 'Apache')
        );
        $this->assertDomEquals(
        	'<option value="PHP" selected="selected">PHP</option>
            <option value="Apache" selected="selected">Apache</option>
            <option value="MySQL">MySQL</option>',
            options_for_select(array('PHP', 'Apache', 'MySQL'), array('PHP', 'Apache'))
        );
        $this->assertDomEquals(
        	'<option value="PHP">PHP</option>
            <option value="&lt;XML&gt;" selected="selected">&lt;XML&gt;</option>',
            options_for_select(array('PHP', '<XML>'), '<XML>')
        );
    }
    
    public function testOptionsForSelectWithAssociativeArray()
    {
        $this->assertDomEquals(
            '<option value="7€">Margharita</option>
            <option value="9€">Calzone</option>
            <option value="8€">Napolitaine</option>',
            options_for_select(array('Margharita'=>'7€', 'Calzone'=>'9€', 'Napolitaine'=>'8€'))
        );
        $this->assertDomEquals(
            '<option value="7€">Margharita</option>
            <option value="9€" selected="selected">Calzone</option>
            <option value="8€">Napolitaine</option>',
            options_for_select(array('Margharita'=>'7€', 'Calzone'=>'9€', 'Napolitaine'=>'8€'), '9€')
        );
        $this->assertDomEquals(
            '<option value="7€">Margharita</option>
            <option value="9€" selected="selected">Calzone</option>
            <option value="8€" selected="selected">Napolitaine</option>',
            options_for_select(array('Margharita'=>'7€', 'Calzone'=>'9€', 'Napolitaine'=>'8€'), array('9€', '8€'))
        );
    }
    
    public function testSelect()
    {
        $services = array('Marketing', 'IT', 'Commercial');
        $this->assertDomEquals(
            '<select name="service">
            <option value="Marketing">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            select('service', $services)
        );
        $this->assertDomEquals(
            '<select name="service">
            <option value="Marketing" selected="selected">Marketing</option>
            <option value="IT">IT</option>
            <option value="Commercial">Commercial</option>
            </select>',
            select('service', $services, 'Marketing')
        );
        $services = array('Marketing'=>1, 'IT'=>2, 'Commercial'=>3);
        $this->assertDomEquals(
            '<select name="service">
            <option value="1" selected="selected">Marketing</option>
            <option value="2">IT</option>
            <option value="3">Commercial</option>
            </select>',
            select('service', $services, 1)
        );
        $this->assertDomEquals(
            '<select name="service">
            <option value=""></option>
            <option value="1" selected="selected">Marketing</option>
            <option value="2">IT</option>
            <option value="3">Commercial</option>
            </select>',
            select('service', $services, 1, array('include_blank' => true))
        );
        $this->assertDomEquals(
            '<select name="service">
            <option value="">Please select</option>
            <option value="1">Marketing</option>
            <option value="2">IT</option>
            <option value="3">Commercial</option>
            </select>',
            select('service', $services, null, array('prompt' => 'Please select'))
        );
        $this->assertDomEquals(
            '<select name="service">
            <option value="1" selected="selected">Marketing</option>
            <option value="2">IT</option>
            <option value="3">Commercial</option>
            </select>',
            select('service', $services, 1, array('prompt' => 'Please select'))
        );
    }
    
    private function assertDomEquals($str1, $str2)
    {
    	$this->assertXmlStringEqualsXmlString("<root>$str1</root>", "<root>$str2</root>");
    }
}
