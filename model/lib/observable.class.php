<?php

abstract class SObservable
{
    //protected $state = Null;
    protected $observers = array();
    protected $callbacks = array();
    
    protected $selfCallbacks = array();
    
    /*public function getState()
    {
        return $this->state;
    }*/
    
    public function setState($state)
    {
        //$this->state = $state;
        $this->notifyObservers($state);
    }
    
    public function addObserver($observer)
    {
        $this->observers[] = $observer;
    }
    
    public function addCallback($observer, $state, $method)
    {
        $this->callbacks[$state][] = array($observer, $method);
    }
    
    public function addSelfCallback($state, $method)
    {
        $this->selfCallbacks[$state][] = $method;
    }
    
    public function notifyObservers($state)
    {
        //$state = $this->getState();
        $this->$state();
        foreach($this->observers as $observer) $observer->update($this, $state);
        if (isset($this->callbacks[$state]))
        {
            foreach($this->callbacks[$state] as $callback) call_user_func($callback);
        }
        if (isset($this->selfCallbacks[$state]))
        {
            foreach($this->selfCallbacks[$state] as $callback) $this->$callback();
        }
    }
}

interface SObserver
{
    public function update($observable, $msg);
}


?>
