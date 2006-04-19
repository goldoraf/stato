<?php

class CsvTest extends UnitTestCase
{
    public function testIteratorWithFile()
    {
        $csv = new SCsvIterator(fopen(CORE_DIR.'/model/test/fixtures/clients.csv', 'r'));
        $this->assertEqual(array('id', 'contract_id', 'name'), $csv->fields());
        $csv->rewind();
        $this->assertEqual(array('id' => 1, 'contract_id' => 1, 'name' => 'apple'), $csv->current());
        $csv->next();
        $this->assertEqual(array('id' => 2, 'contract_id' => 2, 'name' => 'ibm'), $csv->current());
        $csv->next();
        $this->assertEqual(array('id' => 3, 'contract_id' => 0, 'name' => 'mozilla corp.'), $csv->current());
        $csv->next();
        $this->assertFalse($csv->valid());
    }
    
    public function testIteratorWithStream()
    {
        global $str;
        $str = "'id';'contract_id';'name'\n'1';'1';'apple'\n'2';'2';'ibm'\n'3';'0';'mozilla corp.'";
        $csv = new SCsvIterator(fopen('csvstr://str', 'r+'), array('delimiter' => "'"));
        $this->assertEqual(array('id', 'contract_id', 'name'), $csv->fields());
        $csv->rewind();
        $this->assertEqual(array('id' => 1, 'contract_id' => 1, 'name' => 'apple'), $csv->current());
        $csv->next();
        $this->assertEqual(array('id' => 2, 'contract_id' => 2, 'name' => 'ibm'), $csv->current());
        $csv->next();
        $this->assertEqual(array('id' => 3, 'contract_id' => 0, 'name' => 'mozilla corp.'), $csv->current());
        $csv->next();
        $this->assertFalse($csv->valid());
    }
}

?>
