<?php

class StatoTestCase extends UnitTestCase
{
    public function assertNothingThrown()
    {
        return $this->assertTrue(true);
    }
    
    public function __call($method, $args)
    {
        $method = SInflection::camelize($method);
        if (!method_exists($this, $method))
            throw new Exception('Tried to call unknown method '.get_class($this).'::'.$method);
        return call_user_func_array(array($this, $method), $args);
    }
}

?>
