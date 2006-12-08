class <?php echo $controller_class_name; ?> extends ApplicationController
{
    public $models = array('<?php echo $scaffold; ?>');
    public $layout = 'scaffold';
    
    public function index()
    {
        $this-><?php echo $plural_us_name; ?> = <?php echo $model_class_name; ?>::$objects->all();
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
}
