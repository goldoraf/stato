<?php

class Stato_Form_ValidationError extends Exception
{
    protected $args;
    protected $cleanedValue;
    
    public function __construct($message, $args = array(), $cleanedValue = null)
    {
        parent::__construct($message);
        $this->args = $args;
        $this->cleanedValue = $cleanedValue;
    }
    
    public function getArgs()
    {
        return $this->args;
    }
    
    public function getCleanedValue()
    {
        return $this->cleanedValue;
    }
}

class Stato_Form_Field
{
    public $label;
    public $initial;
    public $helpText;
    
    protected $options;
    protected $required;
    protected $errorMessages;
    protected $inputAttrs;
    protected $name = null;
    protected $value = null;
    protected $input = 'Stato_Form_TextInput';
    protected $defaultOptions = array();
    protected $defaultErrorMessages = array();
    protected $baseDefaultOptions = array(
        'required' => false, 'label' => null, 'initial' => null, 
        'help_text' => null, 'error_messages' => array(), 'input_attrs' => array()
    );
    protected $baseDefaultErrorMessages = array(
        'required' => 'This field is required.',
        'invalid'  => 'Enter a valid value.'
    );
    
    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->baseDefaultOptions, $this->defaultOptions, $options);
        $this->errorMessages = array_merge($this->baseDefaultErrorMessages, 
                                           $this->defaultErrorMessages, $this->options['error_messages']);
        
        list($this->required, $this->label, $this->initial, $this->helpText, $this->inputAttrs)
            = array($this->options['required'], $this->options['label'], $this->options['initial'], 
                    $this->options['help_text'], $this->options['input_attrs']);
            
        if (array_key_exists('input', $this->options)) {
            $ref = new ReflectionClass($this->options['input']);
            if (!$ref->isSubclassOf('Stato_Form_Input'))
                throw new Exception($this->options['input'].' is not a subclass of Stato_Form_Input');
                
            $this->input = $this->options['input'];
        }
    }
    
    public function __toString()
    {
        return $this->render($this->name, $this->value);
    }
    
    public function bind($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
        return $this;
    }
    
    public function render($name, $value = null, $htmlAttrs = array())
    {
        $input = $this->getInput();
        $attrs = array_merge($this->getInputAttrs(), $this->inputAttrs, $htmlAttrs);
        if (!empty($attrs)) $input->addAttrs($attrs);
        return $input->render($name, $value);
    }
    
    public function clean($value)
    {
        if ($this->required && $this->isEmpty($value))
            throw new Stato_Form_ValidationError($this->errorMessages['required']);
            
        if ($this->isEmpty($value)) return null;
        
        return $value;
    }
    
    public function getInput()
    {
        $inputClass = $this->input;
        return new $inputClass();
    }
    
    protected function getInputAttrs()
    {
        return array();
    }
    
    protected function isEmpty($value)
    {
        return $value === '' || $value === null;
    }
}

class Stato_Form_CharField extends Stato_Form_Field
{
    protected $regex;
    protected $length;
    protected $minLength;
    protected $maxLength;
    protected $defaultOptions = array(
        'length' => null, 'min_length' => null, 'max_length' => null, 'regex' => null
    );
    protected $defaultErrorMessages = array(
        'length'     => 'Ensure this value has %d characters (it has %d).',
        'min_length' => 'Ensure this value has at least %d characters (it has %d).',
        'max_length' => 'Ensure this value has at most %d characters (it has %d).'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        list($this->regex, $this->length, $this->minLength, $this->maxLength)
            = array($this->options['regex'], $this->options['length'], 
                    $this->options['min_length'], $this->options['max_length']);
    }
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->isEmpty($value)) return '';
        
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        
        if (!is_null($this->regex) && !filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $this->regex))))
            throw new Stato_Form_ValidationError($this->errorMessages['invalid'], array(), $value);
        
        $length = mb_strlen($value);
        
        if (!is_null($this->length) && $length != $this->length)
            throw new Stato_Form_ValidationError($this->errorMessages['length'], array($this->length, $length), $value);
            
        if (!is_null($this->minLength) && $length < $this->minLength)
            throw new Stato_Form_ValidationError($this->errorMessages['min_length'], array($this->minLength, $length), $value);
            
        if (!is_null($this->maxLength) && $length > $this->maxLength)
            throw new Stato_Form_ValidationError($this->errorMessages['max_length'], array($this->maxLength, $length), $value);
        
        return $value;
    }
    
    protected function getInputAttrs()
    {
        if (!is_null($this->maxLength) && in_array($this->input, array('Stato_Form_TextInput', 'Stato_Form_PasswordInput')))
            return array('maxlength' => $this->maxLength);
        
        return parent::getInputAttrs();
    }
}

class Stato_Form_TextField extends Stato_Form_CharField
{
    protected $input = 'Stato_Form_Textarea';
}

class Stato_Form_IntegerField extends Stato_Form_Field
{
    protected $minValue;
    protected $maxValue;
    protected $defaultOptions = array(
        'min_value' => null, 'max_value' => null
    );
    protected $defaultErrorMessages = array(
        'invalid'   => 'Enter a whole number.',
        'min_value' => 'Ensure this value is less than or equal to %s.',
        'max_value' => 'Ensure this value is greater than or equal to %s.'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        list($this->minValue, $this->maxValue)
            = array($this->options['min_value'], $this->options['max_value']);
    }
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->isEmpty($value)) return null;
        
        $value = (int) filter_var((string) $value, FILTER_SANITIZE_NUMBER_INT);
        
        if (!is_null($this->minValue) && $value < $this->minValue)
            throw new Stato_Form_ValidationError($this->errorMessages['min_value'], array($this->minValue), $value);
            
        if (!is_null($this->maxValue) && $value > $this->maxValue)
            throw new Stato_Form_ValidationError($this->errorMessages['max_value'], array($this->maxValue), $value);
        
        return $value;
    }
}

class Stato_Form_FloatField extends Stato_Form_Field
{
    protected $minValue;
    protected $maxValue;
    protected $defaultOptions = array(
        'min_value' => null, 'max_value' => null
    );
    protected $defaultErrorMessages = array(
        'invalid'   => 'Enter a number.',
        'min_value' => 'Ensure this value is less than or equal to %s.',
        'max_value' => 'Ensure this value is greater than or equal to %s.'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        list($this->minValue, $this->maxValue)
            = array($this->options['min_value'], $this->options['max_value']);
    }
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->isEmpty($value)) return null;
        
        $value = (float) filter_var((string) $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_SCIENTIFIC);
        
        if (!is_null($this->minValue) && $value < $this->minValue)
            throw new Stato_Form_ValidationError($this->errorMessages['min_value'], array($this->minValue), $value);
            
        if (!is_null($this->maxValue) && $value > $this->maxValue)
            throw new Stato_Form_ValidationError($this->errorMessages['max_value'], array($this->maxValue), $value);
        
        return $value;
    }
}

/**
 * Validates that the input can be converted to a DateTime object.
 * 
 * Consequently, accepted input formats are that of strtotime() PHP function, 
 * and the returned cleaned value is a DateTime object.
 */
class Stato_Form_DateTimeField extends Stato_Form_Field
{
    protected $defaultErrorMessages = array(
        'invalid'   => 'Enter a valid date.'
    );
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->isEmpty($value)) return null;
        
        if ($value instanceof DateTime) return $value;
        try { 
            $value = filter_var($value, FILTER_SANITIZE_STRING);
            // With PHP 5.3, we could use DateTime::createFromFormat()
            // It will open new opportunities ;)
            $value = new DateTime($value);
        } catch (Exception $e) {
            throw new Stato_Form_ValidationError($this->errorMessages['invalid'], array(), $value);
        }
        return $value;
    }
}

class Stato_Form_EmailField extends Stato_Form_Field
{
    protected $defaultErrorMessages = array(
        'invalid'   => 'Enter a valid e-mail address.'
    );
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->isEmpty($value)) return null;
        
        $value = filter_var($value, FILTER_SANITIZE_EMAIL);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL))
            throw new Stato_Form_ValidationError($this->errorMessages['invalid'], array(), $value);
            
        return $value;
    }
}

class Stato_Form_UrlField extends Stato_Form_Field
{
    protected $defaultErrorMessages = array(
        'invalid'   => 'Enter a valid URL.'
    );
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->isEmpty($value)) return null;
        
        $value = filter_var($value, FILTER_SANITIZE_URL);
        if (!filter_var($value, FILTER_VALIDATE_URL))
            throw new Stato_Form_ValidationError($this->errorMessages['invalid'], array(), $value);
            
        return $value;
    }
}

class Stato_Form_IpField extends Stato_Form_Field
{
    protected $defaultErrorMessages = array(
        'invalid'   => 'Enter a valid IP.'
    );
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->isEmpty($value)) return null;
        
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        if (!filter_var($value, FILTER_VALIDATE_IP))
            throw new Stato_Form_ValidationError($this->errorMessages['invalid'], array(), $value);
            
        return $value;
    }
}

class Stato_Form_BooleanField extends Stato_Form_Field
{
    protected $checkedValue;
    protected $uncheckedValue;
    protected $input = 'Stato_Form_CheckboxInput';
    protected $defaultOptions = array(
        'unchecked_value' => '0', 'checked_value' => '1'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        list($this->checkedValue, $this->uncheckedValue)
            = array($this->options['checked_value'], $this->options['unchecked_value']);
    }
    
    public function clean($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        
        if ($value !== true && $this->required)
            throw new Stato_Form_ValidationError($this->errorMessages['required']);
            
        return $value;
    }
    
    public function render($name, $value = false, $htmlAttrs = array())
    {
        if ($this->input == 'Stato_Form_CheckboxInput') {
            $checkbox = $this->getInput();
            $checkbox->addAttrs(array('checked' => (bool) $value));
            $hidden = new Stato_Form_HiddenInput();
            return $hidden->render($name, $this->uncheckedValue) . $checkbox->render($name, $this->checkedValue, $htmlAttrs);
        }
        return parent::render($name, $value, $htmlAttrs);
    }
}

class Stato_Form_ChoiceField extends Stato_Form_Field
{
    protected $choices;
    protected $input = 'Stato_Form_Select';
    protected $defaultOptions = array(
        'choices' => array()
    );
    protected $defaultErrorMessages = array(
        'invalid_choice' => 'Select a valid choice.'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->choices = $this->options['choices'];
    }
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->isEmpty($value)) return '';
        
        if (!$this->isChoiceValid($value))
            throw new Stato_Form_ValidationError($this->errorMessages['invalid_choice'], array($value));
            
        return $value;
    }
    
    public function getInput()
    {
        $input = parent::getInput();
        $input->setChoices($this->choices);
        return $input;
    }
    
    protected function isChoiceValid($choice)
    {
        $nonAssoc = (key($this->choices) === 0);
        foreach ($this->choices as $k => $v) {
            if (is_array($v)) {
                $nonAssoc2 = (key($v) === 0);
                foreach ($v as $k2 => $v2) {
                    if ($nonAssoc2) $k2 = $v2;
                    if ($choice == $k2) return true;
                }
            } else {
                if ($nonAssoc) $k = $v;
                if ($choice == $k) return true;
            }
        }
        return false;
    }
}

class Stato_Form_MultipleChoiceField extends Stato_Form_ChoiceField
{
    protected $input = 'Stato_Form_MultipleSelect';
    protected $defaultErrorMessages = array(
        'invalid_choice' => 'Select a valid choice.',
        'invalid_list'   => 'Enter a list of values.'
    );
    
    public function clean($value)
    {
        if ($this->required && $this->isEmpty($value))
            throw new Stato_Form_ValidationError($this->errorMessages['required']);
            
        if ($this->isEmpty($value)) return array();
        
        if (!is_array($value))
            throw new Stato_Form_ValidationError($this->errorMessages['invalid_list']);
        
        foreach ($value as $v) {
            if (!$this->isChoiceValid($v))
                throw new Stato_Form_ValidationError($this->errorMessages['invalid_choice'], array($v));
        }
            
        return $value;
    }
}

class Stato_Form_FileField extends Stato_Form_Field
{
    protected $input = 'Stato_Form_FileInput';
    protected $defaultErrorMessages = array(
        'required' => 'A file is required',
        'missing'  => 'No file was submitted.',
        'empty'    => 'The submitted file is empty.',
        'size'     => 'The submitted file exceeds maximum file size.',
        'unknown'  => 'An error occured during file upload. Please try submitting the file again.'
    );
    
    public function clean($value)
    {
        if ($this->required && $this->isEmpty($value))
            throw new Stato_Form_ValidationError($this->errorMessages['required']);
            
        if ($this->isEmpty($value) || !$value instanceof Stato_UploadedFile) return null;
        
        if (!$value->isSafe())
            throw new Stato_Form_ValidationError($this->errorMessages['missing']);
            
        if (!$value->error) {
            if ($value->size === 0)
                throw new Stato_Form_ValidationError($this->errorMessages['empty']);
                
            return $value;
        }
        
        switch ($value->error) {
            case Stato_UploadedFile::SIZE:
                $msg = $this->errorMessages['size'];
                break;
            case Stato_UploadedFile::NO_FILE:
                $msg = $this->errorMessages['missing'];
                break;
            default:
                $msg = $this->errorMessages['unknown'];
        }
        
        throw new Stato_Form_ValidationError($msg);
    }
}
