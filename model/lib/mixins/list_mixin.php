<?php

class SListMixin
{
    public static function registerCallbacks($object)
    {
        //$object->addCallback($object, 'beforeCreate', 'addToListBottom');
        //$object->addCallback($object, 'afterDelete', 'removeFromList');
        $object->addSelfCallback('beforeCreate', 'addToListBottom');
        $object->addSelfCallback('afterDelete', 'removeFromList');
    }
    
    public function insertAt($position = 1)
    {
        $this->insertAtPosition($position);
    }
    
    public function moveLower()
    {
        $lowerItem = $this->lowerItem();
        if ($lowerItem === null) return;
        // transaction...
        $lowerItem->decrementPosition();
        $this->incrementPosition();
    }
    
    public function moveHigher()
    {
        $higherItem = $this->higherItem();
        if ($higherItem === null) return;
        // transaction...
        $higherItem->incrementPosition();
        $this->decrementPosition();
    }
    
    public function moveToBottom()
    {
        if (!$this->isInList()) return;
        // transaction...
        $this->decrementPositionsOnLowerItems();
        $this->assumeBottomPosition();
    }
    
    public function moveToTop()
    {
        if (!$this->isInList()) return;
        // transaction...
        $this->incrementPositionsOnHigherItems();
        $this->assumeTopPosition();
    }
    
    public function removeFromList()
    {
        if ($this->isInList()) $this->decrementPositionsOnLowerItems();
    }
    
    public function incrementPosition()
    {
        if (!$this->isInList()) return;
        $this->updateAttribute($this->positionField(), $this->readAttribute($this->positionField()) + 1);
    }
    
    public function decrementPosition()
    {
        if (!$this->isInList()) return;
        $this->updateAttribute($this->positionField(), $this->readAttribute($this->positionField()) - 1);
    }
    
    public function isInList()
    {
        return $this->readAttribute($this->positionField()) != Null;
    }
    
    public function isFirst()
    {
        if (!$this->isInList()) return false;
        return $this->readAttribute($this->positionField()) == 1;
    }
    
    public function isLast()
    {
        if (!$this->isInList()) return false;
        return $this->readAttribute($this->positionField()) == $this->bottomPosition();
    }
    
    public function higherItem()
    {
        if (!$this->isInList()) return null;
        return SActiveStore::findFirst(
            get_class($this),
            $this->scopeCondition()." AND {$this->positionField()} = ".($this->readAttribute($this->positionField()) - 1)
        );
    }
    
    public function lowerItem()
    {
        if (!$this->isInList()) return null;
        return SActiveStore::findFirst(
            get_class($this),
            $this->scopeCondition()." AND {$this->positionField()} = ".($this->readAttribute($this->positionField()) + 1)
        );
    }
    
    // to owerwrite if necessary
    protected function scopeCondition()
    {
        if (!isset($this->actAs['List']['scope'])) return '1 = 1';
        $scope = $this->actAs['List']['scope'];
        if (ctype_alpha($scope))
        {
            if (!$this->modelExists($scope))
                throw new SException('The scope provided does not seem to be an existent model.');
            
            $scope = strtolower($scope).'_id';
        }
        return $scope." = '".$this->readAttribute($scope)."'";
    }
    
    protected function positionField()
    {
        if (!isset($this->actAs['List']['field'])) return 'position';
        return $this->actAs['List']['field'];
    }
    
    protected function addToListTop()
    {
        $this->incrementPositionsOnAllItems();
    }
    
    protected function addToListBottom()
    {
        $this->writeAttribute($this->positionField(), $this->bottomPosition() + 1);
    }
    
    protected function bottomPosition()
    {
        $item = $this->bottomItem();
        return $item ? $item[$this->positionField()] : 0;
    }
    
    protected function bottomItem()
    {
        return SActiveStore::findFirst(get_class($this), $this->scopeCondition(), array('order' => "{$this->positionField()} DESC"));
    }
    
    protected function assumeBottomPosition()
    {
        $this->updateAttribute($this->positionField(), $this->bottomPosition() + 1);
    }
    
    protected function assumeTopPosition()
    {
        $this->updateAttribute($this->positionField(), 1);
    }
    
    protected function decrementPositionsOnHigherItems($position)
    {
        SActiveStore::updateAll(
            get_class($this),
            "{$this->positionField()} = ({$this->positionField()} - 1)",
            self::scopeCondition()." AND {$this->positionField()} <= {$position}"
        );
    }
    
    protected function decrementPositionsOnLowerItems()
    {
        if (!$this->isInList()) return;
        SActiveStore::updateAll(
            get_class($this),
            "{$this->positionField()} = ({$this->positionField()} - 1)",
            self::scopeCondition()." AND {$this->positionField()} > ".$this->readAttribute($this->positionField())
        );
    }
    
    protected function incrementPositionsOnHigherItems()
    {
        if (!$this->isInList()) return;
        SActiveStore::updateAll(
            get_class($this),
            "{$this->positionField()} = ({$this->positionField()} + 1)",
            self::scopeCondition()." AND {$this->positionField()} < ".$this->readAttribute($this->positionField())
        );
    }
    
    protected function incrementPositionsOnLowerItems($position)
    {
        SActiveStore::updateAll(
            get_class($this),
            "{$this->positionField()} = ({$this->positionField()} + 1)",
            self::scopeCondition()." AND {$this->positionField()} >= {$position}"
        );
    }
    
    protected function incrementPositionsOnAllItems()
    {
        SActiveStore::updateAll(
            get_class($this),
            "{$this->positionField()} = ({$this->positionField()} + 1)",
            self::scopeCondition()
        );
    }
    
    protected function insertAtPosition($position)
    {
        $this->removeFromList();
        $this->incrementPositionsOnLowerItems($position);
        $this->updateAttribute($this->positionField(), $position);
    }
    
    protected function modelExists($className)
    {
        if (class_exists($className) 
            || file_exists(MODULES_DIR.'/'.SContext::$request->module.'/models/'.$className.'.class.php'))
        {
            return True;
        }
        return False;
    }
}

?>
