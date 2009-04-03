<?php

class SFormException extends Exception {}

interface SIFieldDecorator
{
    public function decorate($label, $field, $error = null, $help_text = null);
}

class SFormErrors extends ArrayObject
{
    public function __toString()
    {
        $html = "<ul class=\"errorlist\">\n";
        foreach ($this as $k => $v) $html.= "<li>$v</li>\n";
        $html.= "</ul>";
        return $html;
    }
}

class SForm
{
    public $errors;
    
    protected $data;
    protected $files;
    protected $multipart = false;
    protected $is_bound = false;
    protected $fields = array();
    protected $cleaned_data = array();
    protected $initial_values = array();
    protected $prefix = null;
    protected $field_decorator = null;
    
    public function __construct(array $data = null, array $files = null)
    {
        $this->bind($data, $files);
    }
    
    public function __set($name, SField $field)
    {
        $this->add_field($name, $field);
    }
    
    public function __get($name)
    {
        if (!isset($this->{$name}))
            throw new SFormException('Field not found:'.$name);
            
        return new SBoundField($this, $this->fields[$name], $name);
    }
    
    public function __isset($name)
    {
        return array_key_exists($name, $this->fields);
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
    public function add_field($name, $field, $options = array())
    {
        if (!$field instanceof SField) {
            $field_class = 'S'.$field.'Field';
            if (!class_exists($field_class))
                throw new SFormException($field_class.' class not found');
                
            $field = new $field_class($options);
        }
        if ($field instanceof SFileField) $this->multipart = true;
        $this->fields[$name] = $field;
    }
    
    public function is_bound()
    {
        return $this->is_bound;
    }
    
    public function is_multipart()
    {
        return $this->multipart;
    }
    
    public function render($tag = 'p')
    {
        $open_tag = '<'.$tag.'>';
        $close_tag = '</'.$tag.'>';
        $html = array();
        foreach ($this->fields as $name => $field) {
            $bf = new SBoundField($this, $field, $name);
            $html[] = $open_tag.$bf->label_tag.$bf->render().$close_tag;
        }
        return implode("\n", $html);
    }
    
    public function get_cleaned_data()
    {
        return $this->cleaned_data;
    }
    
    public function get_cleaned_value($name)
    {
        return (array_key_exists($name, $this->cleaned_data)) ? $this->cleaned_data[$name] : null;
    }
    
    public function set_prefix($prefix)
    {
        $this->prefix = $prefix;
    }
    
    public function get_prefix()
    {
        return $this->prefix;
    }
    
    public function set_initial_values(array $values)
    {
        $this->initial_values = $values;
    }
    
    public function get_initial_value($name)
    {
        return (array_key_exists($name, $this->initial_values)) ? $this->initial_values[$name] : null;
    }
    
    public function is_valid(array $data = null, array $files = null)
    {
        if (!$this->is_bound) $this->bind($data, $files);
        if (!$this->is_bound) return;
        
        $this->cleaned_data = array();
        $this->errors = new SFormErrors();
        
        foreach ($this->fields as $name => $field) {
            $value = (array_key_exists($name, $this->data)) ? $this->data[$name] : null;
            try {
                $value = $field->clean($value);
                $this->cleaned_data[$name] = $value;
            } catch (SValidationError $e) {
                $this->errors[$name] = vsprintf(__($e->get_message()), $e->get_args());
                $this->cleaned_data[$name] = $e->get_cleaned_value();
            }
        }
        
        return count($this->errors) === 0;
    }
    
    protected function bind(array $data = null, array $files = null)
    {
        $this->is_bound = (!is_null($data) || !is_null($files));
        $this->data = (!is_null($data)) ? $data : array();
        $this->files = (!is_null($files)) ? $files : array();
    }
}

class SBoundField
{
    public $label;
    public $label_tag;
    public $html_name;
    public $errors;
    public $help_text;
    
    protected $form;
    protected $field;
    protected $name;
    protected $id;
    
    public function __construct(SForm $form, SField $field, $name)
    {
        $this->form = $form;
        $this->field = $field;
        $this->name = $name;
        $this->id = (is_null($prefix = $this->form->get_prefix())) ? $this->name : "{$prefix}_{$name}";
        $this->html_name = (is_null($prefix = $this->form->get_prefix())) ? $this->name : "{$prefix}[{$name}]";
        $this->label = (is_null($this->field->label)) ? $this->get_label() : $this->field->label;
        $this->label_tag = $this->get_label_tag();
        // $this->errors
        $this->help_text = $this->field->help_text;
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
    public function render()
    {
        if (!$this->form->is_bound()) {
            $value = (!is_null($initial = $this->form->get_initial_value($this->name))) 
                   ? $initial : $this->field->initial;
        } else {
            $value = $this->form->get_cleaned_value($this->name);
        }
        return $this->field->render($this->html_name, $value, array('id' => $this->id));
    }
    
    protected function get_label()
    {
        $label = __($this->name);
        if ($label == $this->name) $label = SInflection::humanize($this->name);
        return $label;
    }
    
    protected function get_label_tag()
    {
        return "<label for=\"{$this->id}\">{$this->label}</label>";
    }
}