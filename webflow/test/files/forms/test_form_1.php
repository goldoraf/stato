<?php

class TestForm1 extends SForm
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->lib = new SCharField;
    }
    
    protected function clean()
    {
        if ($this->cleaned_data['lib'] != 'foo')
            throw new SValidationError('Lib should be "foo"');
    }
}