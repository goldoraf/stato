<?php

abstract class SDbSchema
{
    abstract public function define();
    
    public function __call($method, $args)
    {
        if ($method == 'create_table')
            $this->announce('create table '.$args[0]);
        
        return call_user_func_array(array(SActiveRecord::connection(), $method), $args);
    }
    
    protected function announce($message)
    {
        echo "    $message\n";
    }
}

?>
