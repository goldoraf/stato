<?php

class TestForm1 extends Stato_Webflow_Forms_Form
{
    public function __construct(array $data = null, array $files = null)
    {
        parent::__construct($data, $files);
        $this->lib = new Stato_Webflow_Forms_CharField;
    }
    
    protected function clean()
    {
        if ($this->cleanedData['lib'] != 'foo')
            throw new Stato_Webflow_Forms_ValidationError('Lib should be "foo"');
    }
}