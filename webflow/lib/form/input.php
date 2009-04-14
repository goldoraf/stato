<?php

abstract class SInput
{
    protected $attrs;
    protected $type = null;
    
    public function __construct(array $attrs = array())
    {
        $this->attrs = $attrs;
    }
    
    public function add_attrs(array $attrs)
    {
        $this->attrs = array_merge($this->attrs, $attrs);
    }
    
    public function render($name, $value = null, array $attrs = array())
    {
        $final_attrs = array_merge(array('type' => $this->type, 'name' => $name), $this->attrs, $attrs);
        if ($value != '') $final_attrs['value'] = $value;
        return '<input '.$this->flatten_attrs($final_attrs).' />';
    }
    
    protected function flatten_attrs(array $attrs)
    {
        if (count($attrs) == 0) return;
        $set = array();
        foreach($attrs as $key => $value) {
            if ($value !== null && $value !== false) {
                if ($value === true) $set[] = $key.'="'.$key.'"';
                else $set[] = $key.'="'.$value.'"';
            }
        }
        return implode(" ", $set);
    }
}

class STextInput extends SInput
{
    protected $type = 'text';
}

class SPasswordInput extends SInput
{
    protected $type = 'password';
}

class SHiddenInput extends SInput
{
    protected $type = 'hidden';
}

class SFileInput extends SInput
{
    protected $type = 'file';
    
    public function render($name, $value = null, array $attrs = array())
    {
        return parent::render($name, null, $attrs);
    }
}

class STextarea extends SInput
{
    public function __construct(array $attrs = array())
    {
        $this->attrs = array_merge(array('cols' => 40, 'rows' => 10), $attrs);
    }
    
    public function render($name, $value = null, array $attrs = array())
    {
        $final_attrs = array_merge(array('name' => $name), $this->attrs, $attrs);
        if ($value === null) $value = '';
        return '<textarea '.$this->flatten_attrs($final_attrs).'>'.$value.'</textarea>';
    }
}

class SDateInput extends STextInput
{
    protected $format = 'Y-m-d';
    
    public function __construct(array $attrs = array())
    {
        if (array_key_exists('format', $attrs)) {
            $this->format = $attrs['format'];
            unset($attrs['format']);
        }
        parent::__construct($attrs);
    }
    
    public function render($name, $value = null, array $attrs = array())
    {
        if ($value instanceof DateTime) $value = $value->format($this->format);
        return parent::render($name, $value, $attrs);
    }
}

class SDateTimeInput extends SDateInput
{
    protected $format = 'Y-m-d H:i:s';
}

class STimeInput extends SDateInput
{
    protected $format = 'H:i:s';
}

class SCheckboxInput extends SInput
{
    protected $type = 'checkbox';
}

class SSelect extends SInput
{
    protected $choices = array();
    
    public function __construct(array $attrs = array())
    {
        if (array_key_exists('choices', $attrs)) {
            $this->set_choices($attrs['choices']);
            unset($attrs['choices']);
        }
        parent::__construct($attrs);
    }
    
    public function set_choices(array $choices)
    {
        $this->choices = $choices;
    }
    
    public function render($name, $value = null, array $attrs = array())
    {
        $final_attrs = array_merge(array('name' => $name), $this->attrs, $attrs);
        $options = $this->render_options($this->choices, $value);
        return '<select '.$this->flatten_attrs($final_attrs).'>'.$options.'</select>';
    }
    
    protected function render_options($set, $selected = null)
    {
        $str = '';
        $non_assoc = (key($set) === 0);
        if (!is_array($selected)) $selected = array($selected);
        foreach ($set as $value => $lib) {
            if (is_array($lib)) {
                $str.= '<optgroup label="'.html_escape($value).'">'
                    .$this->render_options($lib, $selected).'</optgroup>';
            } else {
                if ($non_assoc) $value = $lib;
                $str.= '<option value="'.html_escape($value).'"';
                if (in_array($value, $selected)) $str.= ' selected="selected"';
                $str.= '>'.html_escape($lib)."</option>\n";
            }
        }
        return $str;
    }
}

class SMultipleSelect extends SSelect
{
    public function __construct(array $attrs = array())
    {
        $attrs['multiple'] = true;
        parent::__construct($attrs);
    }
    
    public function render($name, $value = null, array $attrs = array())
    {
        if (!preg_match('/.*\[\]$/', $name)) $name.= '[]';
        return parent::render($name, $value, $attrs);
    }
}