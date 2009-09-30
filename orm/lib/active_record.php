<?php

class SAdapterNotSpecified extends Exception { }
class SAdapterNotFound extends Exception { }
class SAssociationTypeMismatch extends Exception { }
class SRecordNotSaved extends Exception { }

/**
 * ORM base class
 * 
 * <var>SActiveRecord</var> objects don't specify their attributes directly, 
 * but rather infer them from the table definition with which they're linked. 
 * Any change in the schema of the table is though instantly reflected in the objects.  
 * 
 * @package Stato
 * @subpackage orm
 */
class SActiveRecord extends SObservable implements ArrayAccess/*, SISerializable*/
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
    
    public $record_timestamps = false;
    
    public static $configurations    = null;
    public static $table_name_prefix = null;
    public static $table_name_suffix = null;
    public static $log_sql           = false;
    
    protected static $conn = null;
    protected static $rollback_clones = array();
    
    protected $meta       = null;
    protected $new_record = true;
    
    protected $values         = array();
    protected $changed_values = array();
    
    public function __construct($values = null)
    {
        $this->meta = SMapper::retrieve(get_class($this));
        $this->init_values();
        $this->ensure_proper_type();
        if ($values != null && is_array($values)) $this->populate($values);
    }
    
    public function __sleep()
    {
        foreach (array_keys($this->meta->relationships) as $relation) unset($this->values[$relation]);
        return array('errors', 'attr_protected', 'attr_serialized', 'attr_required', 'attr_unique',
            'attr_unique', 'validations', 'record_timestamps', 'values', 'changed_values', 'meta', 'new_record');
    }
    
    public function __wakeup()
    {
        foreach (array_keys($this->meta->relationships) as $relation) 
            $this->values[$relation] = $this->meta->attributes[$relation]->default_value($this);
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
    
    public function __call($method, $args)
    {
        if (preg_match('/^([_a-zA-Z]\w*)_(has_changed|change|was)$/', $method, $matches))
        {
            $attr_name = $matches[1];
            switch ($matches[2])
            {
                case 'has_changed':
                    return $this->has_attribute_changed($attr_name);
                case 'change':
                    return $this->attribute_change($attr_name);
                case 'was':
                    return $this->attribute_old_value($attr_name);
            }
        }
    }
    
    public function __toString()
    {
        return $this->id;
    }
    
    public function offsetExists($offset)
    {
        return (isset($this->values[$offset]));
    }
    
    public function offsetGet($offset)
    {
        return @$this->values[$offset];
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
     * DEPRECATED : use __toString() instead
     */
    public function __repr()
    {
        return $this->__toString();
    }
    
    public function to_array()
    {
        return $this->values;
    }
    
    public function dump()
    {
        $str = '';
        foreach($this->values as $key => $value)
        {
            if ($value === true)  $value = 'True';
            if ($value === false) $value = 'False';
            if ($value === null)  $value = 'Null';
            $str.= "$key = $value\n";
        }
        return '['.get_class($this)."]\n".$str;
    }
    
    public function serializable_form($options = array())
    {
        $defaults = array('include' => array(), 'exclude' => array());
        $options = array_merge($defaults, $options);
        if (!is_array($options['include']))
            $options['include'] = array($options['include']);
        if (!is_array($options['exclude']))
            $options['exclude'] = array($options['exclude']);
        
        $obj = new stdClass;
        foreach ($this->meta->attributes as $name => $column)
        {
            if (!array_key_exists($name, $this->meta->relationships)
                && !in_array($name, $this->attr_protected)
                && !in_array($name, $options['exclude'])
                && !preg_match('/(_id|_count)$/', $name))
                $obj->$name = (string) $this->$name;
        }
        foreach ($this->meta->relationships as $name => $assoc)
        {
            if (in_array($name, $options['include']))
                $obj->$name = $this->$name->target();
        }
        return $obj;
    }
    
    /**
     * Do any attributes have unsaved changes?
     */
    public function has_changed()
    {
        return count($this->changed_values) != 0;
    }
    
    /**
     * Returns an array of attributes with unsaved changes.
     * <code>$person->changed();   // -> false
     * $person->name = 'john';
     * $person->changed();   // -> array('name')</code>             
     */
    public function changed()
    {
        return array_keys($this->changed_values);
    }
    
    /**
     * Returns an array of changed attributes, with both original and new values.
     * <code>$person->changes();   // -> array()
     * $person->name = 'john';
     * $person->changes();   // -> array('name' => array('bob', 'john'))</code>             
     */
    public function changes()
    {
        $changes = array();
        foreach ($this->changed_values as $k => $v)
            $changes[$k] = array($v, $this->values[$k]);
        return $changes;
    }
    
    /**
     * Does <var>$name</var> attribute have unsaved change?
     */
    public function has_attribute_changed($name)
    {
        return in_array($name, $this->changed());
    }
    
    /**
     * Returns both original and new values of a changed attribute.
     */
    public function attribute_change($name)
    {
        if (!$this->has_attribute_changed($name)) return null;
        return array($this->changed_values[$name], $this->values[$name]);
    }
    
    /**
     * Returns original value of a changed attribute.
     */
    public function attribute_old_value($name)
    {
        return ($this->has_attribute_changed($name)) ? $this->changed_values[$name] : @$this->values[$name];
    }
    
    public function init_values()
    {
        foreach ($this->meta->attributes as $name => $attr) $this->values[$name] = $attr->default_value($this);
    }
    
    /**
     * Sets all attributes at once. Used by <var>SQuerySet</var> class to instantiate records.
     */
    public function hydrate($values=array())
    {
        foreach($values as $key => $value)
        {
            /*if (!is_object($value) && $value !== null && !is_bool($value)) $value = stripslashes($value);
            if (!$this->attr_exists($key)) $this->values[$key] = $value;
            else $this->values[$key] = $this->meta->attributes[$key]->typecast($this, $value);*/
            if ($this->attr_exists($key)) {
                if (in_array($key, $this->attr_serialized)) $value = unserialize($value);
                else $value = $this->meta->attributes[$key]->typecast($this, $value);
            }
            $this->values[$key] = $value;
        }
        $this->set_as_loaded();
    }
    
    /**
     * Sets all attributes at once by passing in an array with keys matching the
     * attribute names. Handles multiparameters attributes (such as dates, which are
     * generally provided as an array when a form is submitted). Attributes defined in
     * <var>$attr_protected</var> property are not affected by this mass-assignment.     
     */
    public function populate($values=array())
    {
        $multi_params_attributes = array();
        $attr_protected = array_merge($this->attr_protected, array_keys($this->meta->relationships));
        
        foreach($values as $key => $value)
        {
            if (is_array($value)) $multi_params_attributes[$key] = $value;
            elseif (!in_array($key, $attr_protected)) $this->$key = $value;
        }
        
        if (!empty($multi_params_attributes)) $this->assign_multiparams_attributes($multi_params_attributes);
    }
    
    /**
     * If the record does not exist yet in the database, creates a new record with
     * values matching those of the object attributes. If the record does exist, updates
     * its values.               
     */
    public function save()
    {
        if (!$this->is_valid()) return false;
        
        if ($this->record_timestamps) $this->save_with_timestamps();
        
        $this->set_state('before_save');
        
        foreach($this->meta->relationships as $k => $v) 
                $this->$k->before_owner_save();
                
        if ($this->is_new_record()) $this->create_record();
        else $this->update_record();
        
        foreach($this->meta->relationships as $k => $v) 
            $this->$k->after_owner_save();
        
        $this->set_state('after_save');
        
        $this->changed_values = array();
        
        return true;
    }
    
    /**
     * Attempts to save the record and throws a <var>SRecordNotSaved</var> exception
     * if it couldn't happen.                
     */
    public function must_save()
    {
        if (!$this->save())
            throw new SRecordNotSaved();
            
        return true;
    }
    
    /**
     * Deletes the record in the database.               
     */
    public function delete()
    {
        $this->set_state('before_delete');
        
        foreach($this->meta->relationships as $k => $v) 
            $this->$k->before_owner_delete();
        
        if ($this->is_new_record()) return false;
        $sql = 'DELETE FROM '.$this->meta->table_name.
               ' WHERE '.$this->meta->identity_field.' = \''.$this->id.'\'';
        $this->conn()->update($sql);
        
        $this->set_state('after_delete');
    }
    
    /**
     * Reloads the attributes of the object from the database.              
     */
    public function reload()
    {
        $qs = new SValuesQuerySet($this->meta);
        $this->values = $qs->get($this->id);
    }
    
    /**
     * Updates all the attributes of the object from the <var>$values</var> array 
     * and saves the record.                    
     */
    public function update_attributes($values)
    {
        $this->populate($values);
        return $this->save();
    }
    
    /**
     * Updates a single attribute of the object and saves the record.                    
     */
    public function update_attribute($name, $value)
    {
        $this->$name = $value;
        return $this->save();
    }
    
    /**
     * Initializes the <var>$attribute</var> to zero if null, adds one and saves the record.
     */
    public function increment($attribute)
    {
        if ($this->$attribute === null) $this->$attribute = 0;
        $this->$attribute += 1;
        return $this->save();
    }
    
    /**
     * Initializes the <var>$attribute</var> to zero if null, substracts one and saves the record.
     */
    public function decrement($attribute)
    {
        if ($this->$attribute === null) $this->$attribute = 0;
        $this->$attribute -= 1;
        return $this->save();
    }
    
    /**
     * Flags the object as been loaded from the database (<var>is_new_record()</var> 
     * will return false). Used by the <var>SQuerySet</var> class when instantiating records.     
     */
    public function set_as_loaded()
    {
        $this->new_record = false;
    }
    
    /**
     * Returns true if a record for the object doesn't exist yet in the database.
     */
    public function is_new_record()
    {
        if (!$this->new_record) return false;
        
        if ($this->new_record && ($id = $this->read_id()) !== null)
            return !$this->conn()->select_one("SELECT 1 FROM {$this->meta->table_name}" 
                                              ." WHERE {$this->meta->identity_field}='$id' LIMIT 1");
        return true;
    }
    
    /**
     * Calls <var>validate()</var> and <var>validate_on_create()</var> or 
     * <var>validate_on_update()</var> and returns true if no errors were added otherwise false. 
     */
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
    
    public function class_name()
    {
        return get_class($this);
    }
    
    /**
     * Returns the typecast value of the attribute identified by <var>$name</var> 
     * (for example, "2007-12-12" in a data column is cast to a <var>SDate</var> object).
     */
    protected function read_attribute($name)
    {
        if (!isset($this->values[$name])) return null;
        return $this->values[$name];
    }
    
    /**
     * Updates the attribute identified by <var>$name</var> with the specified <var>$value</var>
     */
    protected function write_attribute($name, $value)
    {
        if (!array_key_exists($name, $this->changed_values))
        {
            $old = $this->read_attribute($name);
            if ($old != $value) $this->changed_values[$name] = $old;
        }
        if ($this->is_a_relation($name)) $this->values[$name]->replace($value);
        else $this->values[$name] = $value;
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
     *         $this->validate_presence_of(array('username', 'email', 'password'));
     *     }     
     * }
     * </code>                        
     */
    protected function validate_presence_of($attr_names, $options=array())
    {
        if (!is_array($attr_names)) $attr_names = array($attr_names);
        if (!is_array($options)) $options = array('message' => $options);
        foreach ($attr_names as $attr)
            SValidation::validate_presence($this, $attr, $options);
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
    
    public function is_a_relation($name)
    {
        return array_key_exists($name, $this->meta->relationships);
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
                    $this->$key = new SDate($value['year'], $value['month'], $value['day']);
                    break;
                case SColumn::DATETIME:
                    $this->$key = new SDateTime($value['year'], $value['month'], $value['day'],
                                               $value['hour'], $value['min'], $value['sec']);
                    break;
            }
        }
    }
    
    private function create_record()
    {
        $this->set_state('before_create');
        $sql = 'INSERT INTO '.$this->meta->table_name.' '.
               $this->prepare_sql_set();
        $id = $this->conn()->insert($sql);
        if ($this->read_id() === null) $this->id = $id;
        $this->new_record = false;
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
    
    public static function begin_transaction($object = null)
    {
        if (!self::connection()->supports_transactions())
            throw new Exception("{$config['adapter']} adapter does not support transactions");
            
        if ($object !== null) self::$rollback_clones[] = clone $object;
        
        return self::connection()->begin_transaction();
    }
    
    public static function commit()
    {
        return self::connection()->commit();
    }
    
    public static function rollback()
    {
        $return = self::connection()->rollback();
        if (($object = array_pop(self::$rollback_clones)) !== null) return $object;
        return $return;
    }
    
    protected function conn()
    {
        return self::connection();
    }
}

?>
