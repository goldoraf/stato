<?php

class PageManager extends SManager
{
    public function roots()
    {
        return $this->filter("parent_id IS NULL")->order_by('position');
    }
}

class Page extends SActiveRecord
{
    public static $objects;
    public static $decorators = array('tree' => array('order' => 'position'), 
                                      'list' => array('scope' => 'parent'));
    protected $old_full_path;
    
    public function before_validate()
    {
        if (($this->slug == '' || $this->slug === null) && $this->title !== null)
            $this->slug = SInflection::urlize($this->title);
    }
    
    public function validate()
    {
        $this->validate_presence_of('title');
        $this->validate_uniqueness_of('slug', array('scope' => 'parent_id', 
                                                    'message' => 'Ce lien permanent est déjà utilisé !'));
    }
    
    public function before_save()
    {
        $this->old_full_path = $this->full_path;
        $this->create_full_path();
    }
    
    public function after_save()
    {
        $this->update_children_full_path();
    }
    
    public function published_children()
    {
        return $this->children->filter("published='1'");
    }
    
    protected function create_full_path()
    {
        if (!$this->parent->is_null())
            $this->full_path = $this->parent->full_path.'/'.$this->slug;
        else
            $this->full_path = $this->slug;
    }
    
    protected function update_children_full_path()
    {
        if ($this->old_full_path == $this->full_path) return;
        foreach ($this->children->all() as $c) $c->save();
    }
}

?>
