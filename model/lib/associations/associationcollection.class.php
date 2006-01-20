<?php

abstract class AssociationCollection extends Association implements ArrayAccess, Iterator/*, Countable pas avant PHP 5.1 */
{
    protected $target = array();
    protected $ownerNewBeforeSave = false;
    protected $currentIndex = 0;
    
    abstract protected function insertEntity($entity);
    
    abstract protected function deleteEntity($entity);
    
    abstract protected function countEntities($condition);
    
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
    
    public function offsetSet($offset, $entity)
    {
        if ($this->offsetExists($offset))
        {
            $this->checkEntityType($entity);
            if (!$this->owner->isNewRecord()) $this->insertEntity($entity);
            $this->deleteEntity($this->target[$offset]);
            $this->target[$offset] = $entity;
        }
        else $this->add($entity);
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
        $entity = $this->build($attributes);
        if (!$this->owner->isNewRecord()) $entity->save();
        return $entity;
    } 
    
    public function add($entities)
    {
        if (!is_array($entities)) $entities = array($entities);
        $this->loadTarget();
        foreach($entities as $entity)
        {
            $this->checkEntityType($entity);
            if (!$this->owner->isNewRecord()) $this->insertEntity($entity);
            $this->target[] = $entity;
        }
    }
    
    public function delete($entities)
    {
        if (!is_array($entities)) $entities = array($entities);
        $this->loadTarget();
        foreach($entities as $entity)
        {
            $this->checkEntityType($entity);
            if (!$entity->isNewRecord()) $this->deleteEntity($entity);
            unset($this->target[$this->search($entity)]);
        }
    }
    
    public function replace($entities)
    {
        foreach($entities as $entity) $this->checkEntityType($entity);
        $this->target = $entities;
        $this->loaded = true;
    }
    
    public function count($condition=Null)
    {
        if ($this->loaded && $condition == Null) return count($this->target);
        else return $this->countEntities($condition);
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
    
    public function contains($entity)
    {
        if (($key = $this->search($entity)) === false) return false;
        return true;
    }
    
    public function search($entity)
    {
        $this->loadTarget();
        foreach($this->target as $key => $value)
        {
            if ($value->id == $entity->id) return $key;
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
                foreach($this->target as $entity)
                {
                    if ($entity->isNewRecord()) $toSave[] = $entity;
                }
            }
            foreach($toSave as $entity) $this->insertEntity($entity);
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
        foreach($this->target as $entity) $entity->delete();
        $this->target = array();
    }
}

?>
