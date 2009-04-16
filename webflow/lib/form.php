<?php

class Stato_Form_Exception extends Exception {}

interface Stato_Form_IFieldDecorator
{
    public function decorate($label, $field, $error = null, $helpText = null);
}

class Stato_Form_Errors extends ArrayObject
{
    protected $form;
    
    public function __construct(Stato_Form $form)
    {
        $this->form = $form;
        parent::__construct(array());
    }
    
    public function __toString()
    {
        $html = "<ul class=\"errorlist\">\n";
        foreach ($this as $k => $v) {
            if ($k == Stato_Form::FORM_WIDE_ERRORS) {
                $html.= "<li>$v</li>\n";
            } else {
                $html.= "<li>".$this->form->{$k}->labelTag." - $v</li>\n";
            }
        }
        $html.= "</ul>";
        return $html;
    }
}

class Stato_Form implements Iterator
{
    const FORM_WIDE_ERRORS = '_all_';
    
    public $errors;
    
    protected $data;
    protected $files;
    protected $multipart = false;
    protected $isBound = false;
    protected $fields = array();
    protected $cleanedData = array();
    protected $initialValues = array();
    protected $prefix = null;
    protected $fieldDecorator = null;
    
    public function __construct(array $data = null, array $files = null)
    {
        $this->bind($data, $files);
    }
    
    public function __set($name, Stato_Form_Field $field)
    {
        $this->addField($name, $field);
    }
    
    public function __get($name)
    {
        if (!isset($this->{$name}))
            throw new Stato_Form_Exception('Field not found:'.$name);
            
        return new Stato_Form_BoundField($this, $this->fields[$name], $name);
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
        return new Stato_Form_BoundField($this, current($this->fields), $this->key());
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
    
    public function addField($name, $field, $options = array())
    {
        if (!$field instanceof Stato_Form_Field) {
            $fieldClass = 'Stato_Form_'.$field.'Field';
            if (!class_exists($fieldClass))
                throw new Stato_Form_Exception($fieldClass.' class not found');
                
            $field = new $fieldClass($options);
        }
        if ($field instanceof Stato_Form_FileField) $this->multipart = true;
        $this->fields[$name] = $field;
    }
    
    public function isBound()
    {
        return $this->isBound;
    }
    
    public function isMultipart()
    {
        return $this->multipart;
    }
    
    public function render($tag = 'p')
    {
        $openTag = '<'.$tag.'>';
        $closeTag = '</'.$tag.'>';
        $html = array();
        foreach ($this->fields as $name => $field) {
            $bf = new Stato_Form_BoundField($this, $field, $name);
            $err = (!$bf->error) ? '' : '<span class="error">'.$bf->error.'</span>';
            $html[] = $openTag.$bf->labelTag.$bf->render().$err.$closeTag;
        }
        return implode("\n", $html);
    }
    
    public function getCleanedData()
    {
        return $this->cleanedData;
    }
    
    public function getCleanedValue($name)
    {
        return (array_key_exists($name, $this->cleanedData)) ? $this->cleanedData[$name] : null;
    }
    
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
    
    public function getPrefix()
    {
        return $this->prefix;
    }
    
    public function setInitialValues(array $values)
    {
        $this->initialValues = $values;
    }
    
    public function getInitialValue($name)
    {
        return (array_key_exists($name, $this->initialValues)) ? $this->initialValues[$name] : null;
    }
    
    public function isValid(array $data = null, array $files = null)
    {
        if (!$this->isBound) $this->bind($data, $files);
        if (!$this->isBound) return;
        
        $this->cleanedData = array();
        $this->errors = new Stato_Form_Errors($this);
        
        foreach ($this->fields as $name => $field) {
            $value = (array_key_exists($name, $this->data)) ? $this->data[$name] : null;
            try {
                $value = $field->clean($value);
                $this->cleanedData[$name] = $value;
                
                $cleanMethod = 'clean'.$name;
                if (method_exists($this, $cleanMethod)) {
                    $value = $this->$cleanMethod($value);
                    $this->cleanedData[$name] = $value;
                }
            } catch (Stato_Form_ValidationError $e) {
                $this->errors[$name] = vsprintf(__($e->getMessage()), $e->getArgs());
                $this->cleanedData[$name] = $e->getCleanedValue();
            }
        }
        
        try {
            $this->clean();
        } catch (Stato_Form_ValidationError $e) {
            $this->errors[self::FORM_WIDE_ERRORS] = $e->getMessage();
        }
        
        return count($this->errors) === 0;
    }
    
    /**
     * Hook for doing any extra form-wide cleaning after every field been 
     * cleaned. Any Stato_From_ValidationError raised by this method will not be 
     * associated with a particular field.
     */
    protected function clean()
    {
        
    }
    
    protected function bind(array $data = null, array $files = null)
    {
        $this->isBound = (!is_null($data) || !is_null($files));
        $this->data = (!is_null($data)) ? $data : array();
        $this->files = (!is_null($files)) ? $files : array();
    }
}

class Stato_Form_BoundField
{
    public $label;
    public $labelTag;
    public $htmlName;
    public $error;
    public $helpText;
    
    protected $form;
    protected $field;
    protected $name;
    protected $id;
    
    public function __construct(Stato_Form $form, Stato_Form_Field $field, $name)
    {
        $this->form = $form;
        $this->field = $field;
        $this->name = $name;
        $this->id = (is_null($prefix = $this->form->getPrefix())) ? $this->name : "{$prefix}_{$name}";
        $this->htmlName = (is_null($prefix = $this->form->getPrefix())) ? $this->name : "{$prefix}[{$name}]";
        $this->label = (is_null($this->field->label)) ? $this->getLabel() : $this->field->label;
        $this->labelTag = $this->getLabelTag();
        $this->error = (isset($this->form->errors[$name])) ? $this->form->errors[$name] : false;
        $this->helpText = $this->field->helpText;
    }
    
    public function __toString()
    {
        return $this->render();
    }
    
    public function render()
    {
        if (!$this->form->isBound()) {
            $value = (!is_null($initial = $this->form->getInitialValue($this->name))) 
                   ? $initial : $this->field->initial;
        } else {
            $value = $this->form->getCleanedValue($this->name);
        }
        return $this->field->render($this->htmlName, $value, array('id' => $this->id));
    }
    
    protected function getLabel()
    {
        $label = __($this->name);
        if ($label == $this->name) $label = humanize($this->name);
        return $label;
    }
    
    protected function getLabelTag()
    {
        return "<label for=\"{$this->id}\">{$this->label}</label>";
    }
}
