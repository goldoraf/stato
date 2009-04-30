<?php

use Stato\Webflow\Forms as forms;

class TestForm1 extends forms\Form
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->lib = new forms\CharField;
    }
    
    protected function clean()
    {
        if ($this->cleanedData['lib'] != 'foo')
            throw new forms\ValidationError('Lib should be "foo"');
    }
}