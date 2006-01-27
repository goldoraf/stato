<?php

class Validation
{
    public static $validations = array
    (
        'presence', 'uniqueness', 'format', 'length', 'confirmation', 'inclusion', 'exclusion', 'acceptance'
    );
    
    public static $patterns = array
    (
        'alpha', 'alphanum', 'num', 'singleline', 'email', 'ip', 'xml', 'utf8'
    );
    
    public static function validateAttribute($entity, $attr, $method = 'save')
    {
        // required ?
        if (in_array($attr, $entity->attrRequired)) self::validatePresence($entity, $attr);
        // unique ?
        if (in_array($attr, $entity->attrUnique)) self::validateUniqueness($entity, $attr);
        
        if (isset($entity->validations[$attr]))
        {
            foreach ($entity->validations[$attr] as $validation => $options)
            {
                if (!isset($options['on']) || $options['on'] == $method)
                {
                    if (in_array($validation, self::$validations))
                    {
                        $method = 'validate'.ucfirst($validation);
                        self::$method($entity, $attr, $options);
                    }
                    else throw new Exception("The validation rule '$validation' does not exist.");
                }
            }
        }
    }
    
    public static function validatePresence($entity, $attr, $options = array())
    {
        $config = array('message' => 'ERR_VALID_REQUIRED', 'on' => 'save');
        $config = array_merge($config, $options);
        
        if ($entity->$attr == '') self::addError($entity, $attr, $config['message']);
    }
    
    public static function validateUniqueness($entity, $attr, $options = array())
    {
        $config = array('message' => 'ERR_VALID_UNIQUE', 'on' => 'save', 'scope' => Null);
        $config = array_merge($config, $options);
        
        $value = $entity->$attr;
        $conditionsSql = $attr.' '.self::attributeCondition($value);
        $conditionsValues = array($value);
        
        if ($config['scope'] !== Null)
        {
            $scopeValue = $entity->readAttribute($config['scope']);
            $conditionsSql.= ' AND '.$config['scope'].' '.self::attributeCondition($scopeValue);
            $conditionsValues[] = $scopeValue;
        }
        
        if (!$entity->isNewRecord())
        {
            $conditionsSql.= ' AND '.$entity->identityField.' <> ?';
            $conditionsValues[] = $entity->id;
        }
        
        if (ActiveStore::findFirst(get_class($entity), array($conditionsSql, $conditionsValues)))
            self::addError($entity, $attr, $config['message']);
    }
    
    public static function validateFormat($entity, $attr, $options = array())
    {
        $config = array('message' => 'ERR_VALID_FORMAT', 'on' => 'save', 'pattern' => Null);
        $config = array_merge($config, $options);
        
        if ($config['pattern'] === Null)
            throw new Exception('A pattern must be supplied for format validation.');
            
        if (!in_array($config['pattern'], self::$patterns))
            throw new Exception('The pattern provided does not exist.');
        
        $method = 'is'.ucfirst($config['pattern']);
            
        if ($config['message'] === Null) $config['message'] = self::$messages[$method];
        
        if (!self::$method($entity->$attr)) self::addError($entity, $attr, $config['message']);
    }
    
    public static function validateLength($entity, $attr, $options = array())
    {
        $config = array
        (
            'too_long'   => 'ERR_VALID_MAXLENGTH',
            'too_short'  => 'ERR_VALID_MINLENGTH',
            'wrong_size' => 'ERR_VALID_LENGTH'
        );
        $config = array_merge($config, $options);
        
        $length = strlen($entity->$attr);
        
        if (isset($config['length']) && $length != $config['length'])
            self::addError($entity, $attr, $config['wrong_size'], $config['length']);
            
        if (isset($config['min_length']) && $length < $config['min_length'])
            self::addError($entity, $attr, $config['too_short'], $config['min_length']);
        
        if (isset($config['max_length']) && $length > $config['max_length'])
            self::addError($entity, $attr, $config['too_long'], $config['max_length']);
    }
    
    public static function validateInclusion($entity, $attr, $options = array())
    {
        $config = array('message' => 'ERR_VALID_INCLUSION', 'on' => 'save');
        $config = array_merge($config, $options);
        
        if (!isset($config['choices']) || !is_array($config['choices']))
            throw new Exception('An array of choices must be supplied.');
            
        if (!in_array($entity->$attr, $config['choices']))
            self::addError($entity, $attr, $config['message']);
    }
    
    public static function validateExclusion($entity, $attr, $options = array())
    {
        $config = array('message' => 'ERR_VALID_EXCLUSION', 'on' => 'save');
        $config = array_merge($config, $options);
        
        if (!isset($config['choices']) || !is_array($config['choices']))
            throw new Exception('An array of choices must be supplied.');
            
        if (in_array($entity->$attr, $config['choices']))
            self::addError($entity, $attr, $config['message']);
    }
    
    public static function validateConfirmation($entity, $attr, $options = array())
    {
        $config = array('message' => 'ERR_VALID_CONFIRM', 'on' => 'save');
        $config = array_merge($config, $options);
        
        $confirmAttr = $attr.'_confirmation';
        
        if ($entity->$attr != $entity->$confirmAttr)
            self::addError($entity, $attr, $config['message']);
    }
    
    public static function validateAcceptance($entity, $attr, $options = array())
    {
        $config = array('message' => 'ERR_VALID_ACCEPT', 'on' => 'save', 'accept' => '1');
        $config = array_merge($config, $options);
        
        if ($entity->$attr != $config['accept'])
            self::addError($entity, $attr, $config['message']);
    }
    
    public static function isAlpha($data, $options = array())
    {
        return ctype_alpha($data);
    }
    
    public static function isAlphaNum($data, $options = array())
    {
        return ctype_alnum($data);
    }
    
    public static function isNum($data, $options = array())
    {
        return ctype_digit($data);
    }
    
    public static function isSingleline($data, $options = array())
    {
        return ctype_graph($data);
    }
    
    public static function isEmail($data, $options = array())
    {
        if (ereg("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", trim($data))) 
        {
            return True;
        }
        return False;
    }
    
    public static function isIP($data, $options = array())
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
    
    public static function isXML($data, $options = array())
    {
        
    }
    
    public static function isUTF8($data, $options = array())
    {
       if (strlen($str) == 0) return True;
        // If even just the first character can be matched, when the /u
        // modifier is used, then it's valid UTF-8. If the UTF-8 is somehow
        // invalid, nothing at all will match, even if the string contains
        // some valid sequences
        return (preg_match('/^.{1}/us',$str,$ar) == 1);
    }
    
    public static function isValidUTF8($str)
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
       if (!($aCnt = count($ups))) return true; // Empty string *is* valid UTF-8
       for ($i = 1; $i <= $aCnt;)
       {
           if (!($tbytes = $trailing_bytes[($b1 = $ups[$i++])])) continue;
           if ($tbytes == -1) return false;
          
           $first = true;
           while ($tbytes > 0 && $i <= $aCnt)
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
    
    private static function addError($entity, $attr, $message, $var = null)
    {
        $message = Context::locale($message);
        $humanReadableAttr = Context::locale($attr);
        if ($humanReadableAttr == $attr) $humanReadableAttr = str_replace('_', ' ', $attr);
        $message = ucfirst(sprintf($message, $humanReadableAttr, $var));
        if (!isset($entity->errors[$attr])) $entity->errors[$attr] = $message;
    }
    
    private static function attributeCondition($value)
    {
        if ($value === Null) return 'IS ?';
        return '= ?';
    }
}

?>
