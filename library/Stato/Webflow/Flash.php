<?php

namespace Stato\Webflow;

/**
 * Flash class
 * 
 * The flash provides a way to pass temporary variables between actions.
 * Anything you place in the flash will be exposed to the very next action 
 * and then erased. It's useful for doing notices and warnings, such as a create 
 * action that sets <var>$this->flash['notice'] = 'Succesfully created';</var> 
 * before redirecting to a display action that can then display the flash.
 * The flash is available as a property in you controllers.
 * 
 * @package Stato
 * @subpackage Webflow      
 */
class Flash extends \ArrayObject
{
    private $keep = array();
    
    public function offsetSet($k, $v)
    {
        $this->keep($k);
        parent::offsetSet($k, $v);
    }
    
    /**
     * Keeps a specific flash entry available for the next action.
     */
    public function keep($k)
    {
        if (!in_array($k, $this->keep)) $this->keep[] = $k;
    }
    
    /**
     * Deletes all unkept entries.
     * 
     * This method is automatically called by the controller.
     */
    public function discard()
    {
        $discard = array();
        foreach ($this as $k => $v) {
            if (!in_array($k, $this->keep)) $discard[] = $k;
        }
        
        foreach ($discard as $k) $this->offsetUnset($k);
        $this->keep = array();
    }
}