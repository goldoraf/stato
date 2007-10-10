<?php

interface SAroundFilter
{
    public function before($controller);
    
    public function after($controller);
}

?>