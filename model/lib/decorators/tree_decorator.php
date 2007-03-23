<?php

class STreeDecorator extends SActiveRecordDecorator
{
    protected $foreign_key = null;
    protected $order       = null;
    protected $manager     = null;
    
    public function __construct($record, $config = array())
    {
        $this->record       = $record;
        $this->foreign_key  = (isset($config['foreign_key'])) ? $config['foreign_key'] : 'parent_id';
        $this->order        = (isset($config['order'])) ? $config['order']  : null;
        $this->manager      = new SManager(get_class($this->record));
    }
    
    public static function alter_table_map($meta, $config = array())
    {
        $config = array_merge(array('foreign_key' => 'parent_id', 'order' => null), $config);
        $meta->relationships['parent']   = array('assoc_type' => 'belongs_to', 'class_name' => $meta->class, 'foreign_key' => $config['foreign_key']);
        $meta->relationships['children'] = array('assoc_type' => 'has_many', 'class_name' => $meta->class, 'foreign_key' => $config['foreign_key'], 'dependent' => 'delete');
    }
    
    public function roots() // should be static but PHP is not Ruby...
    {
        $qs = $this->manager->filter("{$this->foreign_key} IS NULL");
        if ($this->order !== null) $qs = $qs->order_by($this->order);
        return $qs;
    }
    
    public function ancestors()
    {
        list($node, $nodes) = array($this, array());
        while (!$node->parent->is_null())
        {
            $node = $node->parent->target();
            $nodes[] = $node;
        }
        return $nodes;
    }
    
    public function root()
    {
        $node = $this;
        while (!$node->parent->is_null()) $node = $node->parent->target();
        return $node;
    }
    
    public function siblings()
    {
        $siblings = array();
        foreach ($this->self_and_siblings() as $r)
            if ($r->id != $this->id) $siblings[] = $r;
        return $siblings;
    }
    
    public function self_and_siblings()
    {
        return (!$this->parent->is_null()) ? $this->parent->children->all() : $this->roots();
    }
}

?>
