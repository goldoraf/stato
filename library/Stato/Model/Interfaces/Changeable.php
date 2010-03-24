<?php

namespace Stato\Model\Interfaces;

interface Changeable
{
    public function hasChanged();
    
    public function getChangedProperties();
    
    public function hasPropertyChanged($name);
    
    public function getChanges();
}