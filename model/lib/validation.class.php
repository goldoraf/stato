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
       // From http://w3.org/International/questions/qa-forms-utf-8.html
       /*return preg_match('%^(?:
             [\x09\x0A\x0D\x20-\x7E]            # ASCII
           | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
           |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
           | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
           |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
           |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
           | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
           |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
       )*$%xs', $data);*/
       
       if (strlen($str) == 0) return True;
        // If even just the first character can be matched, when the /u
        // modifier is used, then it's valid UTF-8. If the UTF-8 is somehow
        // invalid, nothing at all will match, even if the string contains
        // some valid sequences
        return (preg_match('/^.{1}/us',$str,$ar) == 1);
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
