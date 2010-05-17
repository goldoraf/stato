<?php

class SFormWizard
{
    protected $step;
    protected $forms;
    protected $request;
    protected $current_form;
    protected $initial_values;
    protected $step_field_name = 'wizard_step';
    
    public function __construct(SRequest $request)
    {
        $this->step = 0;
        $this->forms = array();
        $this->request = $request;
        $this->current_form = null;
        $this->initial_values = array();
    }
    
    public function is_done()
    {
        $current_step = $this->determine_step();
        
        if ($current_step >= $this->count_steps())
            throw new SHttp404("There is no step $current_step in this wizard");
            
        if ($this->request->is_post()) {
            $params = $this->request->params[$this->get_prefix_for_step($current_step)];
            $this->current_form = $this->instantiate_form($current_step, $params);
        } else {
            $this->current_form = $this->instantiate_form($current_step);
        }
            
        if ($this->current_form->is_valid()) {
            $this->process_step($this->current_form, $current_step);
            $next_step = $current_step + 1;
            
            if ($next_step == $this->count_steps()) {
                return true;
            } else {
                $this->current_form = null;
                $this->step = $next_step;
                return false;
            }
        }
        $this->step = $current_step;
        return false;
    }
    
    public function get_form()
    {
        if (is_null($this->current_form)) 
            $this->current_form = $this->instantiate_form($this->step);
        
        return $this->current_form;
    }
    
    public function get_previous_fields()
    {
        $hidden =  new SHiddenInput();
        $prev_fields = array();
        for ($i = 0; $i < $this->step; $i++) {
            $old_data = $this->request->params[$this->get_prefix_for_step($i)];
            foreach ($old_data as $k => $v) {
                $prev_fields[] = $hidden->render($this->get_prefix_for_step($i).'['.$k.']', $v);
            }
        }
        return implode("\n", $prev_fields);
    }
    
    public function get_step_field()
    {
        $hidden =  new SHiddenInput();
        return $hidden->render($this->step_field_name, $this->step);
    }
    
    public function get_step()
    {
        return $this->step + 1;
    }
    
    public function get_partial()
    {
        return 'wizard';
    }
    
    public function get_cleaned_data()
    {
        $data = array();
        for ($i = 0; $i < $this->count_steps(); $i++) {
            $form = $this->instantiate_form($i, $this->request->params[$this->get_prefix_for_step($i)]);
            if (!$form->is_valid()) {
                throw new Exception;
            }
            $data = array_merge($data, $form->cleaned_data);
        }
        return $data;
    }
    
    protected function process_step($form, $step)
    {
        
    }
    
    protected function instantiate_form($step, $data = null)
    {
        $form_class = $this->forms[$step];
        $form = new $form_class($data);
        $form->set_prefix($this->get_prefix_for_step($step));
        $form->set_initial_values($this->get_initial_values_for_step($step));
        return $form;
    }
    
    protected function get_prefix_for_step($step)
    {
        return 'step_'.$step;
    }
    
    protected function get_initial_values_for_step($step)
    {
        return isset($this->initial_values[$step]) ? $this->initial_values[$step] : array();
    }
    
    protected function determine_step()
    {
        if (!$this->request->is_post() || !isset($this->request->params[$this->step_field_name])) return 0;
        return (int) $this->request->params[$this->step_field_name];
    }
    
    protected function count_steps()
    {
        return count($this->forms);
    }
}