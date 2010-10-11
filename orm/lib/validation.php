<?php

class SValidation
{
    const ERR_VALID_FORM      = 'Please correct the following errors :';
    const ERR_VALID_REQUIRED  = '%s is required.';
    const ERR_VALID_UNIQUE    = '%s is taken.';
    const ERR_VALID_FORMAT    = '%s is invalid.';
    const ERR_VALID_LENGTH    = '%s is too long or too short (%d characters required).';
    const ERR_VALID_MINLENGTH = '%s is too short (min is %d characters).';
    const ERR_VALID_MAXLENGTH = '%s is too long (max is %d characters).';
    const ERR_VALID_INCLUSION = '%s is not included in the list.';
    const ERR_VALID_EXCLUSION = '%s is reserved.';
    const ERR_VALID_CONFIRM   = '%s doesn\'t match confirmation.';
    const ERR_VALID_ACCEPT    = '%s must be accepted.';
    
    public static $validations = array
    (
        'presence', 'uniqueness', 'format', 'length', 'confirmation', 'inclusion', 'exclusion', 'acceptance'
    );
    
    public static $patterns = array
    (
        'alpha', 'alphanum', 'num', 'singleline', 'email', 'ip', 'xml', 'utf8'
    );
    
    public static function validate_attribute($record, $attr, $state = 'save')
    {
        // required ?
        if (in_array($attr, $record->attr_required)) self::validate_presence($record, $attr);
        // unique ?
        if (in_array($attr, $record->attr_unique)) self::validate_uniqueness($record, $attr);
        
        if (isset($record->validations[$attr]))
        {
            foreach ($record->validations[$attr] as $validation => $options)
            {
                if (!isset($options['on']) || $options['on'] == $state)
                {
                    if (in_array($validation, self::$validations))
                    {
                        $method = 'validate_'.$validation;
                        self::$method($record, $attr, $options);
                    }
                    else throw new Exception("The validation rule '$validation' does not exist.");
                }
            }
        }
    }
    
    public static function validate_presence($record, $attr, $options = array())
    {
        $config = array('message' => self::ERR_VALID_REQUIRED, 'on' => 'save');
        $config = array_merge($config, $options);
        
        if ($record->$attr === '' || $record->$attr === null) self::add_error($record, $attr, $config['message']);
    }
    
    public static function validate_uniqueness($record, $attr, $options = array())
    {
        $config = array('message' => self::ERR_VALID_UNIQUE, 'on' => 'save', 'scope' => Null);
        $config = array_merge($config, $options);
        
        $value = $record->$attr;
        $meta  = SMapper::retrieve(get_class($record));
        $qs = new SQuerySet($meta);
        $qs = $qs->filter($attr.self::attribute_condition($value), array($value));
        
        if ($config['scope'] !== null)
        {
            $scope_field = $config['scope'];
            $scope_value = $record->$scope_field;
            $qs = $qs->filter($scope_field.self::attribute_condition($scope_value), array($scope_value));
        }
        
        if (!$record->is_new_record())
            $qs = $qs->filter($meta->identity_field.' <> ?', array($record->id));
             
        if ($qs->count() != 0)
            self::add_error($record, $attr, $config['message']);
    }
    
    public static function validate_format($record, $attr, $options = array())
    {
        $config = array('message' => self::ERR_VALID_FORMAT, 'on' => 'save', 'pattern' => Null);
        $config = array_merge($config, $options);
        
        if ($config['pattern'] === Null)
            throw new Exception('A pattern must be supplied for format validation.');
            
        if (in_array($config['pattern'], self::$patterns))
        {
            $method = 'is_'.ucfirst($config['pattern']);
            $bool = self::$method($record->$attr);
        }
        else $bool = (preg_match($config['pattern'], $record->$attr) != 0);
        
        if (!$bool) self::add_error($record, $attr, $config['message']);
    }
    
    public static function validate_length($record, $attr, $options = array())
    {
        $config = array
        (
            'too_long'   => self::ERR_VALID_MAXLENGTH,
            'too_short'  => self::ERR_VALID_MINLENGTH,
            'wrong_size' => self::ERR_VALID_LENGTH
        );
        $config = array_merge($config, $options);
        
        $length = strlen($record->$attr);
        
        if (isset($config['length']) && $length != $config['length'])
            self::add_error($record, $attr, $config['wrong_size'], $config['length']);
            
        if (isset($config['min_length']) && $length < $config['min_length'])
            self::add_error($record, $attr, $config['too_short'], $config['min_length']);
        
        if (isset($config['max_length']) && $length > $config['max_length'])
            self::add_error($record, $attr, $config['too_long'], $config['max_length']);
    }
    
    public static function validate_inclusion($record, $attr, $options = array())
    {
        $config = array('message' => self::ERR_VALID_INCLUSION, 'on' => 'save');
        $config = array_merge($config, $options);
        
        if (!isset($config['choices']) || !is_array($config['choices']))
            throw new Exception('An array of choices must be supplied.');
            
        if (!in_array($record->$attr, $config['choices']))
            self::add_error($record, $attr, $config['message']);
    }
    
    public static function validate_exclusion($record, $attr, $options = array())
    {
        $config = array('message' => self::ERR_VALID_EXCLUSION, 'on' => 'save');
        $config = array_merge($config, $options);
        
        if (!isset($config['choices']) || !is_array($config['choices']))
            throw new Exception('An array of choices must be supplied.');
            
        if (in_array($record->$attr, $config['choices']))
            self::add_error($record, $attr, $config['message']);
    }
    
    public static function validate_confirmation($record, $attr, $options = array())
    {
        $config = array('message' => self::ERR_VALID_CONFIRM, 'on' => 'save');
        $config = array_merge($config, $options);
        
        $confirm_attr = $attr.'_confirmation';
        
        if ($record->$attr != $record[$confirm_attr])
            self::add_error($record, $attr, $config['message']);
    }
    
    public static function validate_acceptance($record, $attr, $options = array())
    {
        $config = array('message' => self::ERR_VALID_ACCEPT, 'on' => 'save', 'accept' => '1');
        $config = array_merge($config, $options);
        
        if ($record->$attr != $config['accept'])
            self::add_error($record, $attr, $config['message']);
    }
    
    public static function is_alpha($data, $options = array())
    {
        return ctype_alpha($data);
    }
    
    public static function is_alphanum($data, $options = array())
    {
        return ctype_alnum($data);
    }
    
    public static function is_num($data, $options = array())
    {
        return ctype_digit($data);
    }
    
    public static function is_singleline($data, $options = array())
    {
        return ctype_graph($data);
    }
    
    public static function is_email($data, $options = array())
    {
        return filter_var($data, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function is_ip($data, $options = array())
    {
        $num = "(\\d|[1-9]\\d|1\\d\\d|2[0-4]\\d|25[0-5])";
        /*
        \\d       => numbers 0-9
        [1-9]\\d  => numbers 10-99
        1\\d\\d   => numbers 100-199
        2[0-4]\\d => numbers 200-249
        25[0-5]   => numbers 250-255
        */
        if (preg_match("/^$num\\.$num\\.$num\\.$num$/", trim($data)))
        {
            return True;
        }
        return False;
    }
    
    public static function is_xml($data, $options = array())
    {
        
    }
    
    public static function is_utf8($data, $options = array())
    {
       if (strlen($str) == 0) return True;
        // If even just the first character can be matched, when the /u
        // modifier is used, then it's valid UTF-8. If the UTF-8 is somehow
        // invalid, nothing at all will match, even if the string contains
        // some valid sequences
        return (preg_match('/^.{1}/us',$str,$ar) == 1);
    }
    
    public static function is_valid_utf8($str)
    {
       // values of -1 represent disalloweded values for the first bytes in current UTF-8
       /*static */$trailing_bytes = array (
           0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
           0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
           0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
           0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
           -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
           -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
           -1,-1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
           2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, 3,3,3,3,3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1
       );
    
       $ups = unpack('C*', $str);
       if (!($a_cnt = count($ups))) return true; // Empty string *is* valid UTF-8
       for ($i = 1; $i <= $a_cnt;)
       {
           if (!($tbytes = $trailing_bytes[($b1 = $ups[$i++])])) continue;
           if ($tbytes == -1) return false;
          
           $first = true;
           while ($tbytes > 0 && $i <= $a_cnt)
           {
               $cbyte = $ups[$i++];
               if (($cbyte & 0xC0) != 0x80) return false;
              
               if ($first)
               {
                   switch ($b1)
                   {
                       case 0xE0:
                           if ($cbyte < 0xA0) return false;
                           break;
                       case 0xED:
                           if ($cbyte > 0x9F) return false;
                           break;
                       case 0xF0:
                           if ($cbyte < 0x90) return false;
                           break;
                       case 0xF4:
                           if ($cbyte > 0x8F) return false;
                           break;
                       default:
                           break;
                   }
                   $first = false;
               }
               $tbytes--;
           }
           if ($tbytes) return false; // incomplete sequence at EOS
       }       
       return true;
    }
    
    private static function add_error($record, $attr, $message, $var = null)
    {
        $human_readable_attr = __($attr);
        
        if ($human_readable_attr == $attr) 
            $human_readable_attr = str_replace('_', ' ', $attr);
        
        $message = _f($message, array($human_readable_attr, $var));
        
        if (!isset($record->errors[$attr])) $record->errors[$attr] = ucfirst($message);
    }
    
    private static function attribute_condition($value)
    {
        if ($value === null) return ' IS ?';
        return ' = ?';
    }
}

?>
