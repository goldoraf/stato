<?php

abstract class SObservable
{
    //protected $state = Null;
    protected $observers = array();
    protected $callbacks = array();
    
    protected $self_callbacks = array();
    
    /*public function get_state()
    {
        return $this->state;
    }*/
    
    public function set_state($state)
    {
        //$this->state = $state;
        $this->notify_observers($state);
    }
    
    public function add_observer($observer)
    {
        $this->observers[] = $observer;
    }
    
    public function add_callback($observer, $state, $method)
    {
        $this->callbacks[$state][] = array($observer, $method);
    }
    
    public function add_self_callback($state, $method)
    {
        $this->self_callbacks[$state][] = $method;
    }
    
    public function notify_observers($state)
    {
        //$state = $this->get_state();
        $this->$state();
        foreach($this->observers as $observer) $observer->update($this, $state);
        if (isset($this->callbacks[$state]))
            foreach($this->callbacks[$state] as $callback) call_user_func($callback);
        
        if (isset($this->self_callbacks[$state]))
            foreach($this->self_callbacks[$state] as $callback) $this->$callback();
    }
}

interface SObserver
{
    public function update($observable, $msg);
}


?>
