<?php

class SPresenter
{
    public function __get($name)
    {
        $acc_method = 'get_'.$name;
        if (method_exists($this, $acc_method)) return $this->$acc_method();
    }
    
    public function __set($name, $value)
    {
        $acc_method = 'set_'.$name;
        if (method_exists($this, $acc_method)) return $this->$acc_method($value);
    }
}

?>