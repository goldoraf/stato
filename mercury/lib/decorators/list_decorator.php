<?php

class SListDecorator extends SActiveRecordDecorator
{
    protected $column  = null;
    protected $scope   = null;
    protected $manager = null;
    
    public function __construct($record, $config = array())
    {
        $this->record  = $record;
        $this->column  = (isset($config['column'])) ? $config['column'] : 'position';
        $this->scope   = (isset($config['scope']))  ? $config['scope']  : null;
        $this->manager = new SManager($this->record->class_name());
        $this->record->add_callback($this, 'before_create', 'add_to_list_bottom');
        $this->record->add_callback($this, 'after_delete', 'remove_from_list');
    }
    
    public function insert_at($position = 1)
    {
        $this->insert_at_position($position);
    }
    
    public function move_lower()
    {
        $lower_item = $this->lower_item();
        if ($lower_item === null) return;
        // transaction...
        $lower_item->decrement_position();
        $this->increment_position();
    }
    
    public function move_higher()
    {
        $higher_item = $this->higher_item();
        if ($higher_item === null) return;
        // transaction...
        $higher_item->increment_position();
        $this->decrement_position();
    }
    
    public function move_to_bottom()
    {
        if (!$this->is_in_list()) return;
        // transaction...
        $this->decrement_positions_on_lower_items();
        $this->assume_bottom_position();
    }
    
    public function move_to_top()
    {
        if (!$this->is_in_list()) return;
        // transaction...
        $this->increment_positions_on_higher_items();
        $this->assume_top_position();
    }
    
    public function remove_from_list()
    {
        if ($this->is_in_list()) $this->decrement_positions_on_lower_items();
    }
    
    public function increment_position()
    {
        if (!$this->is_in_list()) return;
        $this->record->update_attribute($this->column, $this->record->__get($this->column) + 1);
    }
    
    public function decrement_position()
    {
        if (!$this->is_in_list()) return;
        $this->record->update_attribute($this->column, $this->record->__get($this->column) - 1);
    }
    
    public function is_in_list()
    {
        return $this->record->__get($this->column) != Null;
    }
    
    public function is_first()
    {
        if (!$this->is_in_list()) return false;
        return $this->record->__get($this->column) == 1;
    }
    
    public function is_last()
    {
        if (!$this->is_in_list()) return false;
        return $this->record->__get($this->column) == $this->bottom_position();
    }
    
    public function higher_item()
    {
        if (!$this->is_in_list()) return null;
        try {
            return $this->manager->get(
                $this->scope_condition()." AND {$this->column} = ".($this->record->__get($this->column) - 1)
            );
        } catch (SRecordNotFound $e) {
            return null;
        }
    }
    
    public function lower_item()
    {
        if (!$this->is_in_list()) return null;
        try {
            return $this->manager->get(
                $this->scope_condition()." AND {$this->column} = ".($this->record->__get($this->column) + 1)
            );
        } catch (SRecordNotFound $e) {
            return null;
        }
    }
    
    public function add_to_list_top()
    {
        $this->increment_positions_on_all_items();
    }
    
    public function add_to_list_bottom()
    {
        $this->record->__set($this->column, $this->bottom_position() + 1);
    }
    
    protected function scope_condition()
    {
        if ($this->scope === null) return '1 = 1';
        elseif (ctype_alpha($this->scope))
        {
            $fk = strtolower($this->scope).'_id';
            if ($this->record->__get($fk) === null)
                return "$fk IS NULL";
            else
                return $fk."='".$this->record->__get($fk)."'";
        }
    }
    
    protected function bottom_position()
    {
        $item = $this->bottom_item();
        return $item ? $item->{$this->column} : 0;
    }
    
    protected function bottom_item()
    {
        return $this->manager->filter($this->scope_condition())->order_by("-{$this->column}")->first();
    }
    
    protected function assume_bottom_position()
    {
        $this->record->update_attribute($this->column, $this->bottom_position() + 1);
    }
    
    protected function assume_top_position()
    {
        $this->record->update_attribute($this->column, 1);
    }
    
    protected function decrement_positions_on_higher_items($position)
    {
        $this->manager->update_all(
            "{$this->column} = ({$this->column} - 1)",
            $this->scope_condition()." AND {$this->column} <= {$position}"
        );
    }
    
    protected function decrement_positions_on_lower_items()
    {
        if (!$this->is_in_list()) return;
        $this->manager->update_all(
            "{$this->column} = ({$this->column} - 1)",
            $this->scope_condition()." AND {$this->column} > ".$this->record->__get($this->column)
        );
    }
    
    protected function increment_positions_on_higher_items()
    {
        if (!$this->is_in_list()) return;
        $this->manager->update_all(
            "{$this->column} = ({$this->column} + 1)",
            $this->scope_condition()." AND {$this->column} < ".$this->record->__get($this->column)
        );
    }
    
    protected function increment_positions_on_lower_items($position)
    {
        $this->manager->update_all(
            "{$this->column} = ({$this->column} + 1)",
            $this->scope_condition()." AND {$this->column} >= {$position}"
        );
    }
    
    protected function increment_positions_on_all_items()
    {
        $this->manager->update_all(
            "{$this->column} = ({$this->column} + 1)",
            $this->scope_condition()
        );
    }
    
    protected function insert_at_position($position)
    {
        $this->remove_from_list();
        $this->increment_positions_on_lower_items($position);
        $this->update_attribute($this->column, $position);
    }
}

?>
