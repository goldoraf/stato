<?php

use Stato\Webflow\Forms as forms;

class TestForm2 extends forms\Form
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->lib = new forms\CharField;
    }
    
    protected function cleanLib($value)
    {
        if ($value != 'foo')
            throw new forms\ValidationError('Lib should be "foo"');
            
        return $value;
    }
}