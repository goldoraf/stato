<?php

class SFormException extends Exception {}

interface SIFieldDecorator
{
    public function decorate($label, $field, $error = null, $help_text = null);
}

class SFormErrors extends ArrayObject
{
    const FORM_WIDE_ERRORS = '_all_';
    
    protected $prefix = null;
    protected $labels = array();
    
    public function add_error($field, $error, $label = null)
    {
        $this[$field] = $error;
        $this->labels[$field] = $label;
    }
    
    public function add_general_error($error)
    {
        $this[self::FORM_WIDE_ERRORS] = $error;
    }
    
    public function __toString()
    {
        $html = "<ul class=\"errorlist\">\n";
        foreach ($this as $k => $v) {
            if ($k == self::FORM_WIDE_ERRORS) {
                $html.= "<li>$v</li>\n";
            } else {
                $id = $this->get_id($k);
                if (!array_key_exists($k, $this->labels) || is_null($this->labels[$k])) {
                    $label = $this->get_label($k);
                } else {
                    $label = $this->labels[$k];
                }
                $html.= "<li><label for=\"$id\">$label</label>$v</li>\n";
            }
        }
        $html.= "</ul>";
        return $html;
    }
    
    public function set_prefix($prefix)
    {
        $this->prefix = $prefix;
    }
    
    private function get_id($key)
    {
        return is_null($this->prefix) ? $key : implode('_', (array) $this->prefix).'_'.$key;
    }
    
    private function get_label($key)
    {
        $label = __($key);
        if ($label == $key) $label = SInflection::humanize($key);
        return $label;
    }
}

class SForm implements Iterator
{
    public $errors;
    public $cleaned_data = array();
    
    public $required_css_class = 'required';
    public $error_css_class = 'error';
    
    protected $data;
    protected $files;
    protected $multipart = false;
    protected $is_bound = false;
    protected $fields = array();
    protected $initial_values = array();
    protected $prefix = null;
    protected $field_decorator = null;
    
    public function __construct($data = null, $files = null)
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
    
    public function current()
    {
        return new SBoundField($this, current($this->fields), $this->key());
    }
    
    public function key()
    {
        return key($this->fields);
    }
    
    public function next()
    {
        next($this->fields);
    }
    
    public function rewind()
    {
        reset($this->fields);
    }
    
    public function valid()
    {
        return current($this->fields) !== false;
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
    
    public function visible_fields()
    {
        $visible = array();
        foreach ($this as $field) if (!$field->is_hidden) $visible[] = $field;
        return $visible;
    }
    
    public function hidden_fields()
    {
        $hidden = array();
        foreach ($this as $field) if ($field->is_hidden) $hidden[] = $field;
        return $hidden;
    }
    
    public function render_hidden_fields()
    {
        $html = array();
        foreach ($this->hidden_fields() as $f) $html[] = $f->render();
        return implode("\n", $html);
    }
    
    public function is_bound()
    {
        return $this->is_bound;
    }
    
    public function is_multipart()
    {
        return $this->multipart;
    }
    
    public function render()
    {
        return $this->render_as_p();
    }
    
    public function render_as_p()
    {
        return $this->get_html_output('render_p_row');
    }
    
    public function render_as_table()
    {
        return $this->get_html_output('render_table_row');
    }
    
    private function get_html_output($row_render_method)
    {
        $html = array();
        $hidden_fields = array();
        foreach ($this->list_fields() as $name) {
            $field = $this->fields[$name];
            $bf = new SBoundField($this, $field, $name);
            if ($bf->is_hidden) {
                $hidden_fields[] = $bf->render();
            } else {
                $label = $bf->label_tag;
                $field = $bf->render();
                $help  = $bf->help_text;
                $error = (!$bf->error) ? '' : "<span class=\"error\">{$bf->error}</span>\n";
                $css   = empty($bf->css_classes) ? '' : ' class="'.implode(' ', $bf->css_classes).'"';
                
                $html[] = $this->$row_render_method($label, $field, $help, $error, $css);
            }
        }
        if (!empty($hidden_fields)) $html[] = implode("\n", $hidden_fields);
        return implode("\n", $html);
    }
    
    private function render_p_row($label, $field, $help, $error, $css)
    {
        return "{$error}<p{$css}>{$label}{$field}{$help}</p>";
    }
    
    private function render_table_row($label, $field, $help, $error, $css)
    {
        return "<tr{$css}><th>{$label}</th><td>{$error}{$field}{$help}</td></tr>";
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
        $this->errors->set_prefix($this->prefix);
        
        foreach ($this->fields as $name => $field) {
            $value = (array_key_exists($name, $this->data)) ? $this->data[$name] : null;
            try {
                $value = $field->clean($value);
                $this->cleaned_data[$name] = $value;
                
                $clean_method = 'clean_'.$name;
                if (method_exists($this, $clean_method)) {
                    $value = $this->$clean_method($value);
                    $this->cleaned_data[$name] = $value;
                }
            } catch (SValidationError $e) {
                $this->errors->add_error($name, _f($e->get_message(), $e->get_args()), $field->label);
                $this->cleaned_data[$name] = $e->get_cleaned_value();
            }
        }
        
        try {
            $this->clean();
        } catch (SValidationError $e) {
            $this->errors->add_general_error($e->get_message());
        }
        
        return count($this->errors) === 0;
    }
    
    /**
     * Hook for doing any extra form-wide cleaning after every field been 
     * cleaned. Any SValidationError raised by this method will not be 
     * associated with a particular field.
     */
    protected function clean()
    {
        
    }
    
    protected function bind($data = null, $files = null)
    {
        $this->is_bound = (is_array($data) || is_array($files));
        $this->data = (is_array($data)) ? $data : array();
        $this->files = (is_array($files)) ? $files : array();
    }
    
    protected function list_fields()
    {
        return array_keys($this->fields);
    }
}

class SBoundField
{
    public $label;
    public $label_tag;
    public $html_name;
    public $error;
    public $help_text;
    public $is_hidden;
    public $css_classes;
    
    protected $form;
    protected $field;
    protected $name;
    protected $id;
    
    public function __construct(SForm $form, SField $field, $name)
    {
        $this->form = $form;
        $this->field = $field;
        $this->name = $name;
        $this->id = $this->get_id();
        $this->html_name = $this->get_html_name();
        $this->label = (is_null($this->field->label)) ? $this->get_label() : $this->field->label;
        $this->label_tag = $this->get_label_tag();
        $this->error = (isset($this->form->errors[$name])) ? $this->form->errors[$name] : false;
        $this->help_text = $this->field->help_text;
        $this->is_hidden = $this->field->get_input()->is_hidden();
        $this->css_classes = array();
        
        if ($this->field->is_required()) $this->css_classes[] = $this->form->required_css_class;
        if ($this->error !== false) $this->css_classes[] = $this->form->error_css_class;
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
    
    public function render_in($tag, $tag_options)
    {
        if (!array_key_exists('class', $tag_options))
            $tag_options['class'] = $this->css_classes;
        else {
            if (is_array($tag_options['class']))
                $tag_options['class'] = array_merge($this->css_classes, $tag_options['class']);
            elseif (count($this->css_classes) != 0)
                $tag_options['class'].= ' '.implode(' ', $this->css_classes);
        }
        $tag_attributes = tag_options($tag_options);
        return "<{$tag}{$tag_attributes}>{$this->label_tag}".$this->render()."{$this->help_text}</{$tag}>\n";
    }
    
    protected function get_id()
    {
        return is_null($prefix = $this->form->get_prefix()) ? $this->name : implode('_', (array) $prefix).'_'.$this->name;
    }
    
    protected function get_html_name()
    {
        if (is_null($prefix = $this->form->get_prefix())) return $this->name;
        if (is_array($prefix)) {
            $name = array_shift($prefix);
            foreach ($prefix as $p) $name.= is_numeric($p) ? '[]' : "[$p]";
            return "{$name}[{$this->name}]";
        }
        return "{$prefix}[{$this->name}]";
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
