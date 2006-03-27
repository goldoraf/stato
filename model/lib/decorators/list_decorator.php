<?php

class SListDecorator extends SActiveRecordDecorator
{
    protected $column = null;
    protected $scope  = null;
    
    public function __construct($record, $scope = null, $column = 'position')
    {
        $this->record = $record;
        $this->column = $column;
        $this->scope  = $scope;
        $this->record->addCallback($this, 'beforeCreate', 'addToListBottom');
        $this->record->addCallback($this, 'afterDelete', 'removeFromList');
    }
    
    public function insertAt($position = 1)
    {
        $this->insertAtPosition($position);
    }
    
    public function moveLower()
    {
        $lowerItem = new SListDecorator($this->lowerItem(), $this->scope);
        if ($lowerItem === null) return;
        // transaction...
        $lowerItem->decrementPosition();
        $this->incrementPosition();
    }
    
    public function moveHigher()
    {
        $higherItem = new SListDecorator($this->higherItem(), $this->scope);
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
        $this->record->updateAttribute($this->column, $this->record->__get($this->column) + 1);
    }
    
    public function decrementPosition()
    {
        if (!$this->isInList()) return;
        $this->record->updateAttribute($this->column, $this->record->__get($this->column) - 1);
    }
    
    public function isInList()
    {
        return $this->record->__get($this->column) != Null;
    }
    
    public function isFirst()
    {
        if (!$this->isInList()) return false;
        return $this->record->__get($this->column) == 1;
    }
    
    public function isLast()
    {
        if (!$this->isInList()) return false;
        return $this->record->__get($this->column) == $this->bottomPosition();
    }
    
    public function higherItem()
    {
        if (!$this->isInList()) return null;
        return SActiveStore::findFirst(
            get_class($this->record),
            $this->scopeCondition()." AND {$this->column} = ".($this->record->__get($this->column) - 1)
        );
    }
    
    public function lowerItem()
    {
        if (!$this->isInList()) return null;
        return SActiveStore::findFirst(
            get_class($this->record),
            $this->scopeCondition()." AND {$this->column} = ".($this->record->__get($this->column) + 1)
        );
    }
    
    public function addToListTop()
    {
        $this->incrementPositionsOnAllItems();
    }
    
    public function addToListBottom()
    {
        $this->record->__set($this->column, $this->bottomPosition() + 1);
    }
    
    protected function scopeCondition()
    {
        if ($this->scope === null) return '1 = 1';
        elseif (ctype_alpha($this->scope))
        {
            if (!$this->modelExists($this->scope))
                throw new SException('The scope provided does not seem to be an existent model.');
        }
        $fk = strtolower($this->scope).'_id';
        return $fk." = '".$this->record->__get($fk)."'";
    }
    
    protected function bottomPosition()
    {
        $item = $this->bottomItem();
        return $item ? $item[$this->column] : 0;
    }
    
    protected function bottomItem()
    {
        return SActiveStore::findFirst(get_class($this->record), $this->scopeCondition(), array('order' => "{$this->column} DESC"));
    }
    
    protected function assumeBottomPosition()
    {
        $this->record->updateAttribute($this->column, $this->bottomPosition() + 1);
    }
    
    protected function assumeTopPosition()
    {
        $this->record->updateAttribute($this->column, 1);
    }
    
    protected function decrementPositionsOnHigherItems($position)
    {
        SActiveStore::updateAll(
            get_class($this->record),
            "{$this->column} = ({$this->column} - 1)",
            $this->scopeCondition()." AND {$this->column} <= {$position}"
        );
    }
    
    protected function decrementPositionsOnLowerItems()
    {
        if (!$this->isInList()) return;
        SActiveStore::updateAll(
            get_class($this->record),
            "{$this->column} = ({$this->column} - 1)",
            $this->scopeCondition()." AND {$this->column} > ".$this->record->__get($this->column)
        );
    }
    
    protected function incrementPositionsOnHigherItems()
    {
        if (!$this->isInList()) return;
        SActiveStore::updateAll(
            get_class($this->record),
            "{$this->column} = ({$this->column} + 1)",
            $this->scopeCondition()." AND {$this->column} < ".$this->record->__get($this->column)
        );
    }
    
    protected function incrementPositionsOnLowerItems($position)
    {
        SActiveStore::updateAll(
            get_class($this->record),
            "{$this->column} = ({$this->column} + 1)",
            $this->scopeCondition()." AND {$this->column} >= {$position}"
        );
    }
    
    protected function incrementPositionsOnAllItems()
    {
        SActiveStore::updateAll(
            get_class($this->record),
            "{$this->column} = ({$this->column} + 1)",
            $this->scopeCondition()
        );
    }
    
    protected function insertAtPosition($position)
    {
        $this->removeFromList();
        $this->incrementPositionsOnLowerItems($position);
        $this->updateAttribute($this->column, $position);
    }
    
    protected function modelExists($className)
    {
        if (class_exists($className)) return true;
        else
        {
            try { SDependencies::requireDependency('models', $className, get_class($this->record)); }
            catch (Exception $e) { return false; }
        }
        return true;
    }
}

?>
