<?php

class SimpleExceptionCatcherInvoker extends SimpleInvokerDecorator
{
    public function invoke($method)
    {
        try { parent::invoke($method); }
        catch (Exception $e)
        {
            $test_case = &$this->getTestCase();
            $test_case->exception($e);
        }
    }
}

class StatoTestCase extends UnitTestCase
{
    public function &createInvoker()
    {
        return new SimpleExceptionCatcherInvoker(new SimpleInvoker($this));
    }
    
    public function exception($e)
    {
        $this->_runner->paintFail(
                "Uncaught exception [{$e->getMessage()}] in [{$e->getFile()}] line [{$e->getLine()}]");
    }
    
    public function assertNothingThrown()
    {
        return $this->assertTrue(true);
    }
}

?>
