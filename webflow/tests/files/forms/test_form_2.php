<?php

class TestForm2 extends Stato_Form
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->lib = new Stato_Form_CharField;
    }
    
    protected function cleanLib($value)
    {
        if ($value != 'foo')
            throw new Stato_Form_ValidationError('Lib should be "foo"');
            
        return $value;
    }
}