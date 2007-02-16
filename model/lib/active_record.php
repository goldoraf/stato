<?php

class SAdapterNotSpecified extends Exception { }
class SAdapterNotFound extends Exception { }
class SAssociationTypeMismatch extends Exception { }

/**
 * ORM class
 * 
 * <var>SActiveRecord</var> objects don't specify their attributes directly, 
 * but rather infer them from the table definition with which they're linked. 
 * Any change in the schema of the table is though instantly reflected in the objects.  
 * 
 * @package Stato
 * @subpackage model
 */
class SActiveRecord extends SObservable implements ArrayAccess
{
    public $errors          = array();
    public $attr_protected  = array();
    public $attr_serialized = array();
    
    /**
     * DEPRECATED
     */
    public $attr_required  = array();
    /**
     * DEPRECATED
     */
    public $attr_unique    = array();
    /**
     * DEPRECATED
     */
    public $validations    = array();
    
    public $record_timestamps = False;
    
    public static $configurations    = null;
    public static $table_name_prefix = null;
    public static $table_name_suffix = null;
    public static $log_sql           = false;
    
    protected static $conn = null;
    protected $values      = array();
    protected $meta        = null;
    
    public function __construct($values = null)
    {
        $this->meta = SMapper::retrieve(get_class($this));
        $this->ensure_proper_type();
        if ($values != null && is_array($values)) $this->populate($values);
    }
    
    public function __get($name)
    {
        $acc_method = 'read_'.$name;
        if (method_exists($this, $acc_method)) return $this->$acc_method();
        else return $this->read_attribute($name);
    }
    
    public function __set($name, $value)
    {
        $acc_method = 'write_'.$name;
        if (method_exists($this, $acc_method)) return $this->$acc_method($value);
        else return $this->write_attribute($name, $value);
    }
    
    public function __toString()
    {
        $str = '';
        foreach($this->values as $key => $value)
        {
            if ($value === True) $value = 'True';
            if ($value === False) $value = 'False';
            if ($value === Null) $value = 'Null';
            $str.= "$key = $value\n";
        }
        return '['.get_class($this)."]\n".$str;
    }
    
    public function to_array()
    {
        return $this->values;
    }
    
    public function __repr()
    {
        return $this->id;
    }
    
    public function offsetExists($offset)
    {
        return (isset($this->values[$offset]));
    }
    
    public function offsetGet($offset)
    {
        return $this->values[$offset];
    }
    
    public function offsetSet($offset, $value)
    {
        $this->values[$offset] = $value;
    }
    
    public function offsetUnset($offset)
    {
        $this->values[$offset] = null;
    }
    
    /**
     * Sets all attributes at once by passing in an array with keys matching the
     * attribute names.
     */
    public function populate($values=array())
    {
        $multi_params_attributes = array();
        
        foreach($values as $key => $value)
        {
            if (is_array($value)) $multi_params_attributes[$key] = $value;
            elseif (!in_array($key, array_merge($this->attr_protected, array_keys($this->meta->relationships))))
            {
                if (!is_object($value) && $value !== null && !is_bool($value)) $this->$key = stripslashes($value);
                else $this->$key = $value;
            }
            if (!$this->attr_exists($key)) $this->values[$key] = $value;
        }
        
        if (!empty($multi_params_attributes)) $this->assign_multiparams_attributes($multi_params_attributes);
    }
    
    public function save()
    {
        if (!$this->is_valid()) return false;
        
        if ($this->record_timestamps) $this->save_with_timestamps();
        
        foreach($this->meta->relationships as $k => $v) 
                $this->$k->before_owner_save();
        
        $this->set_state('before_save');
        if ($this->is_new_record()) $this->create_record();
        else $this->update_record();
        $this->set_state('after_save');
        
        foreach($this->meta->relationships as $k => $v) 
            $this->$k->after_owner_save();
        
        return true;
    }
    
    public function delete()
    {
        foreach($this->meta->relationships as $k => $v) 
            $this->$k->before_owner_delete();
        
        $this->set_state('before_delete');
        if ($this->is_new_record()) return false;
        $sql = 'DELETE FROM '.$this->meta->table_name.
               ' WHERE '.$this->meta->identity_field.' = \''.$this->id.'\'';
        $this->conn()->update($sql);
        $this->set_state('after_delete');
    }
    
    public function update_attributes($values)
    {
        $this->populate($values);
        return $this->save();
    }
    
    public function update_attribute($name, $value)
    {
        $this->$name = $value;
        return $this->save();
    }
    
    public function is_new_record()
    {
        $id = $this->read_id();
        if ($id !== null)
            return !$this->conn()->select_one("SELECT 1 FROM {$this->meta->table_name} 
                                             WHERE {$this->meta->identity_field}='$id' LIMIT 1");
        return true;
    }
    
    public function is_valid()
    {
        $this->errors = array();
        $this->set_state('before_validate');
        $this->run_validations('save');
        $this->validate();
        if ($this->is_new_record())
        {
            $this->run_validations('create');
            $this->validate_on_create();
        }  
        else
        {
            $this->run_validations('update');
            $this->validate_on_update();
        }
        $this->set_state('after_validate');
        return empty($this->errors);
    }
    
    /**
     * Overwrite this method for check validations on all saves
     */
    public function validate()
    {
    
    }
    
    /**
     * Overwrite this method for check validations on creation
     */
    public function validate_on_create()
    {
    
    }
    
    /**
     * Overwrite this method for check validations on updates
     */
    public function validate_on_update()
    {
    
    }
    
    public function assigned_values()
    {
        return $this->values;
    }
    
    public function content_attributes()
    {
        if ($this->record_timestamps) 
            return $this->meta->content_attributes(array('created_on', 'updated_on'));
        
        return $this->meta->content_attributes();
    }
    
    protected function read_attribute($name)
    {
        if (!$this->attr_exists($name)) return;
        if (!isset($this->values[$name]))
            $this->values[$name] = $this->meta->attributes[$name]->default_value($this);
        if (in_array($name, $this->attr_serialized) && is_string($this->values[$name]))
            return unserialize($this->values[$name]);
        else
            return $this->values[$name];
    }
    
    protected function write_attribute($name, $value)
    {
        if (!$this->attr_exists($name)) return;
        $this->values[$name] = $this->meta->attributes[$name]->typecast($this, $value);
    }
    
    protected function before_create() {}
    
    protected function after_create() {}
    
    protected function before_update() {}
    
    protected function after_update() {}
    
    protected function before_save() {}
    
    protected function after_save() {}
    
    protected function before_delete() {}
    
    protected function after_delete() {}
    
    protected function before_validate() {}
    
    protected function after_validate() {}
    
    /**
     * Validates that the specified attributes are not blank. 
     * 
     * By blank, understand not null and not empty.
     * Example :
     * <code>
     * class User extends SActiveRecord
     * {
     *     public function validate()
     *     {
     *         $this->validate_presence_of('username', 'email', 'password');
     *     }     
     * }
     * </code>                        
     */
    protected function validate_presence_of()
    {
        $attr_names = func_get_args();
        foreach ($attr_names as $attr)
            SValidation::validate_presence($this, $attr);
    }
    
    /**
     * Validates whether the value of the specified attribute is unique in the table.
     * 
     * You can limit the scope of the uniquness constraint with the 'scope' option.
     * When the record is created, a check is performed to make sure that no record 
     * exists in the table with the given value for the specified attribute. When 
     * the record is updated, the same check is made but disregarding the record itself.     
     * Example :
     * <code>
     * class User extends SActiveRecord
     * {
     *     public function validate()
     *     {
     *         $this->validate_uniqueness_of('username', array('scope' => 'cluster_id'));
     *     }     
     * }
     * </code>
     * Options:
     * - <var>message</var> - A custom error message.
     * - <var>scope</var> - A column by which to limit the scope of the uniqueness constraint.
     */
    protected function validate_uniqueness_of($attr_name, $options = array())
    {
        SValidation::validate_uniqueness($this, $attr_name, $options);
    }
    
    /**
     * Validates whether the value of the specified attribute is of the correct form 
     * by matching it against the regular expression provided.
     * 
     * <var>pattern</var> option can be a regex, or one of the following patterns : <var>alpha</var>, 
     * <var>alphanum</var>, <var>num</var>, <var>singleline</var>, <var>email</var>, <var>ip</var>, <var>xml</var>, <var>utf8</var>.
     * Example :
     * <code>
     * class User extends SActiveRecord
     * {
     *     public function validate()
     *     {
     *         $this->validate_format_of('mail', array('pattern' => 'email'));
     *     }     
     * }
     * </code>
     * Options:
     * - <var>message</var> - A custom error message.
     * - <var>pattern</var> - A regex.                         
     */
    protected function validate_format_of($attr_name, $options = array())
    {
        SValidation::validate_format($this, $attr_name, $options);
    }
    
    /**
     * Validates that the specified attribute matches the length restrictions supplied.
     * 
     * <code>
     * class User extends SActiveRecord
     * {
     *     public function validate()
     *     {
     *         $this->validate_length_of('first_name', array('max_length' => 30));
     *         $this->validate_length_of('last_name', array('max_length' => 30, 'message' => 'less than %d characters...'));
     *         $this->validate_length_of('password', array('min_length' => 6, 'max_length' => 12, 'message' => 'Between 6 and 12 chars plz...'));    
     *         $this->validate_length_of('username', array('min_length' => 4, 'max_length' => 30, 'too_long' => 'Choose a shorter name', 'too_short' => 'Choose a longer name'));      
     *     }     
     * }
     * </code>
     * Options:
     * - <var>message</var> - A custom error message.
     * - <var>min_length</var>
     * - <var>max_length</var>
     * - <var>too_long</var> - The error message if the attribute goes over the maximum.  
     * - <var>too_short</var> - The error message if the attribute goes under the minimum.                         
     */
    protected function validate_length_of($attr_name, $options = array())
    {
        SValidation::validate_length($this, $attr_name, $options);
    }
    
    /**
     * Validates whether the value of the specified attribute is available in an array of choices.
     * 
     * <code>
     * class User extends SActiveRecord
     * {
     *     public function validate()
     *     {
     *         $this->validate_inclusion_of('sex', array('choices' => array('m', 'f'), 'message' => 'Uuuuuh ?!?'));
     *     }     
     * }
     * </code>
     * Options:
     * - <var>message</var> - A custom error message.
     * - <var>choices</var> - An array of available items.                  
     */
    protected function validate_inclusion_of($attr_name, $options = array())
    {
        SValidation::validate_inclusion($this, $attr_name, $options);
    }
    
    /**
     * Validates that the value of the specified attribute is not in an array of choices.
     * 
     * <code>
     * class User extends SActiveRecord
     * {
     *     public function validate()
     *     {
     *         $this->validate_exclusion_of('username', array('choices' => array('admin', 'root')));
     *     }     
     * }
     * </code>
     * Options:
     * - <var>message</var> - A custom error message.
     * - <var>choices</var> - An array of unavailable items.                  
     */
    protected function validate_exclusion_of($attr_name, $options = array())
    {
        SValidation::validate_exclusion($this, $attr_name, $options);
    }
    
    /**
     * Encapsulates the pattern of wanting to validate a password or email address field with a confirmation.
     * 
     * <code>
     * class User extends SActiveRecord
     * {
     *     public function validate()
     *     {
     *         $this->validate_confirmation_of('password');
     *     }     
     * }
     * 
     * // in the view :
     * <?= $f->password_field('password'); ? >
     * <?= $f->password_field('password_confirmation'); ? >
     * </code>     
     * 
     * The 'password_confirmation' attribute is entirely virtual. No database column is needed.                                    
     */
    protected function validate_confirmation_of($attr_name, $options = array())
    {
        SValidation::validate_confirmation($this, $attr_name, $options);
    }
    
    /**
     * Encapsulates the pattern of wanting to validate the acceptance of a terms of service check box.
     * 
     * <code>
     * class User extends SActiveRecord
     * {
     *     public function validate()
     *     {
     *         $this->validate_acceptance_of('terms_of_service');
     *     }     
     * }             
     * </code>
     * 
     * The 'terms_of_service' attribute is entirely virtual. No database column is needed.                
     */
    protected function validate_acceptance_of($attr_name, $options = array())
    {
        SValidation::validate_acceptance($this, $attr_name, $options);
    }
    
    private function run_validations($method)
    {
        foreach (array_keys($this->values) as $key)
            SValidation::validate_attribute($this, $key, $method);
    }
    
    private function attr_exists($name)
    {
        return array_key_exists($name, $this->meta->attributes);
    }
    
    private function read_id()
    {
        return $this->read_attribute($this->meta->identity_field);
    }
    
    private function write_id($value)
    {
        $this->write_attribute($this->meta->identity_field, $value);
    }
    
    /**
     * Creates string values for all attributes that needs more than one single parameter
     * (such as Dates).
     */
    private function assign_multiparams_attributes($params)
    {
        $errors = array();
        foreach($params as $key => $value)
        {
            if (in_array($key, $this->attr_serialized))
            {
                $this->$key = $value;
                continue;
            }
            
            $type = $this->meta->attributes[$key]->type;
            switch ($type)
            {
                case SColumn::DATE:
                    $this->$key = $value['year'].'-'.$value['month'].'-'.$value['day'];
                    break;
                case SColumn::DATETIME:
                    $this->$key = $value['year'].'-'.$value['month'].'-'.$value['day']
                                  .' '.$value['hour'].':'.$value['min'].':'.$value['sec'];
                    break;
            }
        }
    }
    
    private function create_record()
    {
        $this->set_state('before_create');
        $sql = 'INSERT INTO '.$this->meta->table_name.' '.
               $this->prepare_sql_set();
        $this->id = $this->conn()->insert($sql);
        $this->set_state('after_create');
    }
    
    private function update_record()
    {
        $this->set_state('before_update');
        $sql = 'UPDATE '.$this->meta->table_name.' '.
               $this->prepare_sql_set().
               ' WHERE '.$this->meta->identity_field.' = \''.$this->id.'\'';
        $this->conn()->update($sql);
        $this->set_state('after_update');
    }
    
    private function prepare_sql_set()
    {
        $set = array();
        foreach ($this->attributes_with_quotes() as $column => $value)
            $set[] = "`$column` = $value";
        
        return 'SET '.join(',', $set);
    }
    
    private function attributes_with_quotes()
    {
        $quoted = array();
        foreach ($this->meta->attributes as $name => $column)
        {
            if (array_key_exists($name, $this->meta->relationships)) continue;
            if (in_array($name, $this->attr_serialized)) $value = serialize($this->$name);
            else $value = $this->$name;
            $quoted[$name] = $this->conn()->quote($value, $column->type);
        }
        return $quoted;
    }
    
    private function save_with_timestamps()
    {
        $t = SDateTime::today();
        if ($this->is_new_record())
            if ($this->attr_exists('created_on')) $this->values['created_on'] = $t;
        
        if ($this->attr_exists('updated_on')) $this->values['updated_on'] = $t;
    }
    
    private function ensure_proper_type()
    {
        if ($this->meta->descends_from() != 'SActiveRecord')
            $this->write_attribute($this->meta->inheritance_field, SInflection::underscore(get_class($this)));
    }
    
    /**
     * CONNECTION MANAGEMENT ===================================================
     **/
    public static function establish_connection($config = null)
    {
        if ($config === null)
        {
            if (!defined('STATO_ENV')) throw new SAdapterNotSpecified;
            self::establish_connection(STATO_ENV);
        }
        elseif (is_string($config))
        {
            if (isset(self::$configurations[$config]))
                self::establish_connection(self::$configurations[$config]);
            else
                throw new SAdapterNotSpecified("$config database is not configured");
        }
        else
        {
            if (!isset($config['adapter']))
                throw new SAdapterNotSpecified("database configuration does not specify adapter");
            
            $adapter_class = 'S'.$config['adapter'].'Adapter';
            if (!class_exists($adapter_class, false)) 
                throw new SAdapterNotFound("database configuration specifies nonexistent {$config['adapter']} adapter");
                
            self::$conn = new $adapter_class($config);
        }
    }
    
    public static function connection()
    {
        if (!isset(self::$conn)) self::establish_connection();
        return self::$conn;
    }
    
    public static function connection_benchmark()
    {
        if (!isset(self::$conn)) return 0;
        return self::$conn->runtime;
    }
    
    protected function conn()
    {
        return self::connection();
    }
}

?>
