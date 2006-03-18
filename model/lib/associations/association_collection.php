<?php

abstract class SAssociationCollection extends SAssociation implements ArrayAccess, Iterator/*, Countable pas avant PHP 5.1 */
{
    protected $target = array();
    protected $ownerNewBeforeSave = false;
    protected $currentIndex = 0;
    
    abstract protected function insertRecord($record);
    
    abstract protected function deleteRecord($record);
    
    abstract protected function countRecords($condition);
    
    /**
     * ArrayAccess methods
     **/
    public function offsetExists($offset)
    {
        $this->loadTarget();
        if (isset($this->target[$offset])) return true;
        return false;
    }
    
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) return $this->target[$offset];
        return false;
    }
    
    public function offsetSet($offset, $record)
    {
        if ($this->offsetExists($offset))
        {
            $this->checkRecordType($record);
            if (!$this->owner->isNewRecord()) $this->insertRecord($record);
            $this->deleteRecord($this->target[$offset]);
            $this->target[$offset] = $record;
        }
        else $this->add($record);
        return;
    }
    
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset))
        {
            
        }
        return;
    }
    
    /**
     * Iterator methods
     **/
    public function current()
    {
        $this->loadTarget();
        return $this->offsetGet($this->currentIndex);
    }

    public function key()
    {
        return $this->currentIndex;
    }

    public function next()
    {
        return $this->currentIndex++;
    }

    public function rewind()
    {
        $this->currentIndex = 0;
    }

    public function valid()
    {
        if($this->offsetExists($this->currentIndex)) return true;
        return false;
    }
    
    /**
     * Public methods
     **/
    public function create($attributes=array())
    {
        $record = $this->build($attributes);
        if (!$this->owner->isNewRecord()) $record->save();
        return $record;
    } 
    
    public function add($records)
    {
        if (!is_array($records)) $records = array($records);
        $this->loadTarget();
        foreach($records as $record)
        {
            $this->checkRecordType($record);
            if (!$this->owner->isNewRecord()) $this->insertRecord($record);
            $this->target[] = $record;
        }
    }
    
    public function delete($records)
    {
        if (!is_array($records)) $records = array($records);
        $this->loadTarget();
        foreach($records as $record)
        {
            $this->checkRecordType($record);
            if (!$record->isNewRecord()) $this->deleteRecord($record);
            unset($this->target[$this->search($record)]);
        }
    }
    
    public function replace($records)
    {
        foreach($records as $record) $this->checkRecordType($record);
        $this->target = $records;
        $this->loaded = true;
    }
    
    public function count($condition=Null)
    {
        if ($this->loaded && $condition == Null) return count($this->target);
        else return $this->countRecords($condition);
    }
    
    public function clear()
    {
        $this->loadTarget();
        if (count($this->target) == 0) return;
        /*if (isset($this->options['exclusively_dependent']))
            $this->deleteAll();
        else*/
            $this->unsetAll();
    }
    
    public function find()
    {
    
    }
    
    
    public function isEmpty()
    {
        return (count($this->target) == 0);
    }
    
    public function contains($record)
    {
        if (($key = $this->search($record)) === false) return false;
        return true;
    }
    
    public function search($record)
    {
        $this->loadTarget();
        foreach($this->target as $key => $value)
        {
            if ($value->id == $record->id) return $key;
        }
        return false;
    }
    
    public function beforeOwnerSave()
    {
        if ($this->owner->isNewRecord()) $this->ownerNewBeforeSave = true;
    }
    
    /**
     * Callback methods
     **/
    public function afterOwnerSave()
    {
        //if ($this->isLoaded()) // PB
        {
            if ($this->ownerNewBeforeSave) $toSave = $this->target;
            else
            {
                $toSave = array();
                foreach($this->target as $record)
                {
                    if ($record->isNewRecord()) $toSave[] = $record;
                }
            }
            foreach($toSave as $record) $this->insertRecord($record);
        }
    }
    
    public function beforeOwnerDelete() {}
    
    public function afterOwnerDelete() {}
    
    /**
     * Protected methods
     **/
    protected function reset()
    {
        $this->target = array();
        $this->loaded = false;
    }
    
    protected function unsetAll()
    {
        $this->target = array();
    }
    
    protected function deleteAll()
    {
        foreach($this->target as $record) $record->delete();
        $this->target = array();
    }
}

?>
