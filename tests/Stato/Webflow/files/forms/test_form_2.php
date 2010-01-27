<?php

class TestForm2 extends \Stato\Webflow\Forms\Form
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->lib = new \Stato\Webflow\Forms\CharField;
    }
    
    protected function cleanLib($value)
    {
        if ($value != 'foo')
            throw new \Stato\Webflow\Forms\ValidationError('Lib should be "foo"');
            
        return $value;
    }
}