<?php

class SValidationError extends Exception
{
    protected $args;
    protected $cleaned_value;
    
    public function __construct($message, $args = array(), $cleaned_value = null)
    {
        parent::__construct($message);
        $this->args = $args;
        $this->cleaned_value = $cleaned_value;
    }
    
    public function get_message()
    {
        return $this->getMessage();
    }
    
    public function get_args()
    {
        return $this->args;
    }
    
    public function get_cleaned_value()
    {
        return $this->cleaned_value;
    }
}

class SField
{
    public $label;
    public $initial;
    public $help_text;
    
    protected $options;
    protected $required;
    protected $error_messages;
    protected $input_attrs;
    protected $name = null;
    protected $value = null;
    protected $input = 'STextInput';
    protected $default_options = array();
    protected $default_error_messages = array();
    protected $base_default_options = array(
        'required' => false, 'label' => null, 'initial' => null, 
        'help_text' => null, 'error_messages' => array(), 'input_attrs' => array()
    );
    protected $base_default_error_messages = array(
        'required' => 'This field is required.',
        'invalid'  => 'Enter a valid value.'
    );
    
    public function __construct(array $options = array())
    {
        $this->options = array_merge($this->base_default_options, $this->default_options, $options);
        $this->error_messages = array_merge($this->base_default_error_messages, 
                                           $this->default_error_messages, $this->options['error_messages']);
        
        list($this->required, $this->label, $this->initial, $this->help_text, $this->input_attrs)
            = array($this->options['required'], $this->options['label'], $this->options['initial'], 
                    $this->options['help_text'], $this->options['input_attrs']);
        
        if (array_key_exists('input', $this->options)) {
            $ref = new ReflectionClass($this->options['input']);
            if (!$ref->isSubclassOf('SInput'))
                throw new SFormException($this->options['input'].' is not a subclass of SInput');
                
            $this->input = $this->options['input'];
        }
    }
    
    public function __toString()
    {
        return $this->render($this->name, $this->value);
    }
    
    public function is_required()
    {
        return $this->required;
    }
    
    public function bind($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
        return $this;
    }
    
    public function render($name, $value = null, $html_attrs = array())
    {
        $input = $this->get_input();
        $attrs = array_merge($this->get_input_attrs(), $this->input_attrs, $html_attrs);
        if (!empty($attrs)) $input->add_attrs($attrs);
        return $input->render($name, $value);
    }
    
    public function clean($value)
    {
        if ($this->required && $this->is_empty($value))
            throw new SValidationError($this->error_messages['required']);
            
        if ($this->is_empty($value)) return null;
        
        return $value;
    }
    
    public function get_input()
    {
        $input_class = $this->input;
        return new $input_class();
    }
    
    protected function get_input_attrs()
    {
        return array();
    }
    
    protected function is_empty($value)
    {
        return $value === '' || $value === null;
    }
}

class SCharField extends SField
{
    protected $regex;
    protected $length;
    protected $min_length;
    protected $max_length;
    protected $default_options = array(
        'length' => null, 'min_length' => null, 'max_length' => null, 'regex' => null
    );
    protected $default_error_messages = array(
        'length'     => 'Ensure this value has %d characters (it has %d).',
        'min_length' => 'Ensure this value has at least %d characters (it has %d).',
        'max_length' => 'Ensure this value has at most %d characters (it has %d).'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        list($this->regex, $this->length, $this->min_length, $this->max_length)
            = array($this->options['regex'], $this->options['length'], 
                    $this->options['min_length'], $this->options['max_length']);
    }
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->is_empty($value)) return '';
        
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        
        if (!is_null($this->regex) && !filter_var($value, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $this->regex))))
            throw new SValidationError($this->error_messages['invalid'], array(), $value);
        
        $length = mb_strlen($value);
        
        if (!is_null($this->length) && $length != $this->length)
            throw new SValidationError($this->error_messages['length'], array($this->length, $length), $value);
            
        if (!is_null($this->min_length) && $length < $this->min_length)
            throw new SValidationError($this->error_messages['min_length'], array($this->min_length, $length), $value);
            
        if (!is_null($this->max_length) && $length > $this->max_length)
            throw new SValidationError($this->error_messages['max_length'], array($this->max_length, $length), $value);
        
        return $value;
    }
    
    protected function get_input_attrs()
    {
        if (!is_null($this->max_length) && in_array($this->input, array('STextInput', 'SPasswordInput')))
            return array('maxlength' => $this->max_length);
        
        return parent::get_input_attrs();
    }
}

class STextField extends SCharField
{
    protected $input = 'STextarea';
}

class SIntegerField extends SField
{
    protected $min_value;
    protected $max_value;
    protected $default_options = array(
        'min_value' => null, 'max_value' => null
    );
    protected $default_error_messages = array(
        'invalid'   => 'Enter a whole number.',
        'min_value' => 'Ensure this value is less than or equal to %s.',
        'max_value' => 'Ensure this value is greater than or equal to %s.'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        list($this->min_value, $this->max_value)
            = array($this->options['min_value'], $this->options['max_value']);
    }
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->is_empty($value)) return null;
        
        $value = (int) filter_var((string) $value, FILTER_SANITIZE_NUMBER_INT);
        
        if (!is_null($this->min_value) && $value < $this->min_value)
            throw new SValidationError($this->error_messages['min_value'], array($this->min_value), $value);
            
        if (!is_null($this->max_value) && $value > $this->max_value)
            throw new SValidationError($this->error_messages['max_value'], array($this->max_value), $value);
        
        return $value;
    }
}

class SFloatField extends SField
{
    protected $min_value;
    protected $max_value;
    protected $default_options = array(
        'min_value' => null, 'max_value' => null
    );
    protected $default_error_messages = array(
        'invalid'   => 'Enter a number.',
        'min_value' => 'Ensure this value is less than or equal to %s.',
        'max_value' => 'Ensure this value is greater than or equal to %s.'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        list($this->min_value, $this->max_value)
            = array($this->options['min_value'], $this->options['max_value']);
    }
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->is_empty($value)) return null;
        
        $value = (float) filter_var((string) $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_SCIENTIFIC);
        
        if (!is_null($this->min_value) && $value < $this->min_value)
            throw new SValidationError($this->error_messages['min_value'], array($this->min_value), $value);
            
        if (!is_null($this->max_value) && $value > $this->max_value)
            throw new SValidationError($this->error_messages['max_value'], array($this->max_value), $value);
        
        return $value;
    }
}

/**
 * Validates that the input can be converted to a DateTime object.
 * 
 * Consequently, accepted input formats are that of strtotime() PHP function, 
 * and the returned cleaned value is a DateTime object.
 */
class SDateTimeField extends SField
{
    protected $input = 'SDateTimeInput';
    protected $default_error_messages = array(
        'invalid'   => 'Enter a valid date.'
    );
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->is_empty($value)) return null;
        
        if ($value instanceof DateTime) return $value;
        try { 
            $value = filter_var($value, FILTER_SANITIZE_STRING);
            // With PHP 5.3, we could use DateTime::createFromFormat()
            // It will open new opportunities ;)
            $value = new DateTime($value);
        } catch (Exception $e) {
            throw new SValidationError($this->error_messages['invalid'], array(), $value);
        }
        return $value;
    }
}

class SEmailField extends SField
{
    protected $default_error_messages = array(
        'invalid'   => 'Enter a valid e-mail address.'
    );
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->is_empty($value)) return null;
        
        $value = filter_var($value, FILTER_SANITIZE_EMAIL);
        if (!filter_var($value, FILTER_VALIDATE_EMAIL))
            throw new SValidationError($this->error_messages['invalid'], array(), $value);
            
        return $value;
    }
}

class SUrlField extends SField
{
    protected $default_error_messages = array(
        'invalid'   => 'Enter a valid URL.'
    );
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->is_empty($value)) return null;
        
        $value = filter_var($value, FILTER_SANITIZE_URL);
        if (!filter_var($value, FILTER_VALIDATE_URL))
            throw new SValidationError($this->error_messages['invalid'], array(), $value);
            
        return $value;
    }
}

class SIpField extends SField
{
    protected $default_error_messages = array(
        'invalid'   => 'Enter a valid IP.'
    );
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->is_empty($value)) return null;
        
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        if (!filter_var($value, FILTER_VALIDATE_IP))
            throw new SValidationError($this->error_messages['invalid'], array(), $value);
            
        return $value;
    }
}

class SBooleanField extends SField
{
    protected $checked_value;
    protected $unchecked_value;
    protected $input = 'SCheckboxInput';
    protected $default_options = array(
        'unchecked_value' => '0', 'checked_value' => '1'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        list($this->checked_value, $this->unchecked_value)
            = array($this->options['checked_value'], $this->options['unchecked_value']);
    }
    
    public function clean($value)
    {
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        
        if ($value !== true && $this->required)
            throw new SValidationError($this->error_messages['required']);
            
        return $value;
    }
    
    public function render($name, $value = false, $html_attrs = array())
    {
        if ($this->input == 'SCheckboxInput') {
            $checkbox = $this->get_input();
            $checkbox->add_attrs(array('checked' => (bool) $value));
            $hidden = new SHiddenInput();
            return $hidden->render($name, $this->unchecked_value) . $checkbox->render($name, $this->checked_value, $html_attrs);
        }
        return parent::render($name, $value, $html_attrs);
    }
}

class SChoiceField extends SField
{
    protected $choices;
    protected $input = 'SSelect';
    protected $default_options = array(
        'choices' => array()
    );
    protected $default_error_messages = array(
        'invalid_choice'   => 'Select a valid choice.'
    );
    
    public function __construct(array $options = array())
    {
        parent::__construct($options);
        $this->choices = $this->options['choices'];
    }
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->is_empty($value)) return '';
        
        if (!$this->is_choice_valid($value))
            throw new SValidationError($this->error_messages['invalid_choice'], array($value));
            
        return $value;
    }
    
    public function get_input()
    {
        $input = parent::get_input();
        $input->set_choices($this->choices);
        return $input;
    }
    
    protected function is_choice_valid($choice)
    {
        $non_assoc = (key($this->choices) === 0);
        foreach ($this->choices as $k => $v) {
            if (is_array($v)) {
                $non_assoc2 = (key($v) === 0);
                foreach ($v as $k2 => $v2) {
                    if ($non_assoc2) $k2 = $v2;
                    if ($choice == $k2) return true;
                }
            } else {
                if ($non_assoc) $k = $v;
                if ($choice == $k) return true;
            }
        }
        return false;
    }
}

class SMultipleChoiceField extends SChoiceField
{
    protected $input = 'SMultipleSelect';
    protected $default_error_messages = array(
        'invalid_choice' => 'Select a valid choice.',
        'invalid_list'   => 'Enter a list of values.'
    );
    
    public function clean($value)
    {
        if ($this->required && $this->is_empty($value))
            throw new SValidationError($this->error_messages['required']);
            
        if ($this->is_empty($value)) return array();
        
        if (!is_array($value))
            throw new SValidationError($this->error_messages['invalid_list']);
        
        foreach ($value as $v) {
            if (!$this->is_choice_valid($v))
                throw new SValidationError($this->error_messages['invalid_choice'], array($v));
        }
            
        return $value;
    }
}

class SFileField extends SField
{
    protected $input = 'SFileInput';
    protected $default_error_messages = array(
        'required' => 'A file is required',
        'missing'  => 'No file was submitted.',
        'empty'    => 'The submitted file is empty.',
        'size'     => 'The submitted file exceeds maximum file size.',
        'unknown'  => 'An error occured during file upload. Please try submitting the file again.'
    );
    
    public function clean($value)
    {
        if ($this->required && $this->is_empty($value))
            throw new SValidationError($this->error_messages['required']);
            
        if ($this->is_empty($value) || !$value instanceof SUploadedFile) return null;
        
        if (!$value->is_safe())
            throw new SValidationError($this->error_messages['missing']);
            
        if (!$value->error) {
            if ($value->size === 0)
                throw new SValidationError($this->error_messages['empty']);
                
            return $value;
        }
        
        switch ($value->error) {
            case SUploadedFile::SIZE:
                $msg = $this->error_messages['size'];
                break;
            case SUploadedFile::NO_FILE:
                $msg = $this->error_messages['missing'];
                break;
            default:
                $msg = $this->error_messages['unknown'];
        }
        
        throw new SValidationError($msg);
    }
}
