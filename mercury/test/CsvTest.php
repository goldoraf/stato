<?php

require_once dirname(__FILE__) . '/../../test/tests_helper.php';

class CsvTest extends PHPUnit_Framework_TestCase
{
    public function test_iterator_with_file()
    {
        $csv = new SCsvIterator(fopen(STATO_CORE_PATH.'/mercury/test/fixtures/clients.csv', 'r'));
        $this->assertEquals(array('id', 'name'), $csv->fields());
        $csv->rewind();
        $this->assertEquals(array('id' => 1, 'name' => 'apple'), $csv->current());
        $csv->next();
        $this->assertEquals(array('id' => 2, 'name' => 'ibm'), $csv->current());
        $csv->next();
        $this->assertEquals(array('id' => 3, 'name' => 'mozilla corp.'), $csv->current());
        $csv->next();
        $this->assertFalse($csv->valid());
    }
    
    public function test_iterator_with_stream()
    {
        global $str;
        $str = "'id';'contract_id';'name'\n'1';'1';'apple'\n'2';'2';'ibm'\n'3';'0';'mozilla corp.'";
        $csv = new SCsvIterator(fopen('csvstr://str', 'r+'), array('delimiter' => "'"));
        $this->assertEquals(array('id', 'contract_id', 'name'), $csv->fields());
        $csv->rewind();
        $this->assertEquals(array('id' => 1, 'contract_id' => 1, 'name' => 'apple'), $csv->current());
        $csv->next();
        $this->assertEquals(array('id' => 2, 'contract_id' => 2, 'name' => 'ibm'), $csv->current());
        $csv->next();
        $this->assertEquals(array('id' => 3, 'contract_id' => 0, 'name' => 'mozilla corp.'), $csv->current());
        $csv->next();
        $this->assertFalse($csv->valid());
    }
}

