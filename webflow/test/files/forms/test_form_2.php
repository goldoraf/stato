<?php

class TestForm2 extends SForm
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->lib = new SCharField;
    }
    
    protected function clean_lib($value)
    {
        if ($value != 'foo')
            throw new SValidationError('Lib should be "foo"');
            
        return $value;
    }
}