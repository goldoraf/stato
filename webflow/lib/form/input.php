<?php

abstract class Stato_Form_Input
{
    protected $attrs;
    protected $type = null;
    
    public function __construct(array $attrs = array())
    {
        $this->attrs = $attrs;
    }
    
    public function addAttrs(array $attrs)
    {
        $this->attrs = array_merge($this->attrs, $attrs);
    }
    
    public function render($name, $value = null, array $attrs = array())
    {
        $finalAttrs = array_merge(array('type' => $this->type, 'name' => $name), $this->attrs, $attrs);
        if ($value != '') $finalAttrs['value'] = $value;
        return '<input '.$this->flattenAttrs($finalAttrs).' />';
    }
    
    protected function flattenAttrs(array $attrs)
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

class Stato_Form_TextInput extends Stato_Form_Input
{
    protected $type = 'text';
}

class Stato_Form_PasswordInput extends Stato_Form_Input
{
    protected $type = 'password';
}

class Stato_Form_HiddenInput extends Stato_Form_Input
{
    protected $type = 'hidden';
}

class Stato_Form_FileInput extends Stato_Form_Input
{
    protected $type = 'file';
    
    public function render($name, $value = null, array $attrs = array())
    {
        return parent::render($name, null, $attrs);
    }
}

class Stato_Form_Textarea extends Stato_Form_Input
{
    public function __construct(array $attrs = array())
    {
        $this->attrs = array_merge(array('cols' => 40, 'rows' => 10), $attrs);
    }
    
    public function render($name, $value = null, array $attrs = array())
    {
        $finalAttrs = array_merge(array('name' => $name), $this->attrs, $attrs);
        if ($value === null) $value = '';
        return '<textarea '.$this->flattenAttrs($finalAttrs).'>'.$value.'</textarea>';
    }
}

class Stato_Form_DateInput extends Stato_Form_TextInput
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

class Stato_Form_DateTimeInput extends Stato_Form_DateInput
{
    protected $format = 'Y-m-d H:i:s';
}

class Stato_Form_TimeInput extends Stato_Form_DateInput
{
    protected $format = 'H:i:s';
}

class Stato_Form_CheckboxInput extends Stato_Form_Input
{
    protected $type = 'checkbox';
}

class Stato_Form_Select extends Stato_Form_Input
{
    protected $choices = array();
    
    public function __construct(array $attrs = array())
    {
        if (array_key_exists('choices', $attrs)) {
            $this->setChoices($attrs['choices']);
            unset($attrs['choices']);
        }
        parent::__construct($attrs);
    }
    
    public function setChoices(array $choices)
    {
        $this->choices = $choices;
    }
    
    public function render($name, $value = null, array $attrs = array())
    {
        $finalAttrs = array_merge(array('name' => $name), $this->attrs, $attrs);
        $options = $this->renderOptions($this->choices, $value);
        return '<select '.$this->flattenAttrs($finalAttrs).'>'.$options.'</select>';
    }
    
    protected function renderOptions($set, $selected = null)
    {
        $str = '';
        $nonAssoc = (key($set) === 0);
        if (!is_array($selected)) $selected = array($selected);
        foreach ($set as $value => $lib) {
            if (is_array($lib)) {
                $str.= '<optgroup label="'.html_escape($value).'">'
                    .$this->renderOptions($lib, $selected).'</optgroup>';
            } else {
                if ($nonAssoc) $value = $lib;
                $str.= '<option value="'.html_escape($value).'"';
                if (in_array($value, $selected)) $str.= ' selected="selected"';
                $str.= '>'.html_escape($lib)."</option>\n";
            }
        }
        return $str;
    }
}

class Stato_Form_MultipleSelect extends Stato_Form_Select
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