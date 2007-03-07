class <?php echo $controller_class_name; ?> extends ApplicationController
{
    protected $helpers = array('scaffold_ajax');
    protected $layout  = 'scaffold_ajax';
    
    public function index()
    {
        $qs = <?php echo $model_class_name; ?>::$objects->all();
        $qs = $this->order_by_current_key($qs, array_keys(SMapper::retrieve('<?php echo $model_class_name; ?>')->content_attributes()));
        
        list($this-><?php echo $plural_us_name; ?>_pages, $this-><?php echo $plural_us_name; ?>) = $this->paginate($qs, 20);
            
        if ($this->request->is_xhr()) $this->render_partial('objects_list');
    }
    
    public function view()
    {
        $this-><?php echo $singular_us_name; ?> = <?php echo $model_class_name; ?>::$objects->get($this->params['id']);
    }
    
    public function create()
    {
        if (!$this->request->is_post())
        {
            $this-><?php echo $singular_us_name; ?> = new <?php echo $model_class_name; ?>();
        }
        else
        {
            $this-><?php echo $singular_us_name; ?> = new <?php echo $model_class_name; ?>($this->params['<?php echo $singular_us_name; ?>']);
            if ($this-><?php echo $singular_us_name; ?>->save())
            {
                $this->flash['notice'] = '<?php echo $model_class_name; ?> was successfully created !';
                $this->redirect_to(array('action' => 'index'));
                return;
            }
        }
    }
    
    public function update()
    {
        if (!$this->request->is_post())
        {
            $this-><?php echo $singular_us_name; ?> = <?php echo $model_class_name; ?>::$objects->get($this->params['id']);
        }
        else
        {
            $this-><?php echo $singular_us_name; ?> = <?php echo $model_class_name; ?>::$objects->get($this->params['<?php echo $singular_us_name; ?>']['id']);
            if ($this-><?php echo $singular_us_name; ?>->update_attributes($this->params['<?php echo $singular_us_name; ?>']))
            {
                $this->flash['notice'] = '<?php echo $model_class_name; ?> was successfully updated !';
                $this->redirect_to(array('action' => 'index'));
                return;
            }
        }
    }
    
    public function delete()
    {
        <?php echo $model_class_name; ?>::$objects->get($this->params['id'])->delete();
        $this->flash['notice'] = '<?php echo $model_class_name; ?> was successfully deleted !';
        $this->redirect_to(array('action' => 'index'));
    }
    
    protected function order_by_current_key($query_set, $sort_keys, $table_name = null)
    {
        $sort = null;
        foreach ($sort_keys as $value)
        {
            if ($this->params['sort'] == $value) $sort = $value;
            elseif ($this->params['sort'] == $value.'_reverse') $sort = '-'.$value;
        }
        if ($sort === null) $sort = 'id';
        if ($table_name !== null) $sort = $table_name.'.'.$sort;
        
        return $query_set->order_by($sort);
    }
}
