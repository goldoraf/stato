<?php

class SAssociationTypeMismatch extends SException { }

class SActiveRecordMeta
{
    public $class            = null;
    public $underscored      = null;
    public $table_name        = null;
    public $identity_field    = 'id';
    public $inheritance_field = 'type';
    public $attributes       = array();
    public $relationships    = array();
    
    private static $cache = array();
    
    public static function add_manager_to_class($class)
    {
        $ref = new ReflectionClass($class);
        if ($ref->hasProperty('objects')) $ref->setStaticPropertyValue('objects', new SManager($class));
    }
    
    public static function retrieve($class)
    {
        if (!isset(self::$cache[$class]))
        {
            $meta_class = $class.'Meta';
            self::$cache[$class] = new SActiveRecordMeta($class);
        }
        return self::$cache[$class];
    }
    
    public static function reset_meta_information($class)
    {
        unset(self::$cache[$class]);
    }
    
    public function __construct($class)
    {
        $this->class = $class;
        $this->underscored = SInflection::underscore($class);
        $this->get_meta_from_class();
        if ($this->table_name === null) $this->reset_table_name();
        $this->attributes = array_merge(
            SActiveRecord::connection()->columns($this->table_name),
            $this->instantiate_associations()
        );
    }
    
    public function reset_table_name()
    {
        if (($parent = $this->descends_from()) == 'SActiveRecord')
            $this->table_name = SInflection::pluralize(SInflection::underscore($this->class));
        else
            $this->table_name = SInflection::pluralize(SInflection::underscore($parent));
            
        if (SActiveRecord::$table_name_prefix !== null)
            $this->table_name = SActiveRecord::$table_name_prefix.'_'.$this->table_name;
        if (SActiveRecord::$table_name_suffix !== null)
            $this->table_name.= '_'.SActiveRecord::$table_name_suffix;
    }
    
    public function descends_from()
    {
        $ref = new ReflectionClass($this->class);
        return $ref->getParentClass()->getName();
    }
    
    protected function instantiate_associations()
    {
        $assocs = array();
        foreach ($this->relationships as $name => $options) 
            $assocs[$name] = new SAssociation(SAssociationMeta::get_instance($this, $name, $options));
        return $assocs;
    }
    
    protected function get_meta_from_class()
    {
        $ref = new ReflectionClass($this->class);
        $props = array('table_name', 'identity_field', 'inheritance_field', 'relationships');
        foreach ($props as $p) 
            if ($ref->hasProperty($p)) $this->$p = $ref->getStaticPropertyValue($p);
    }
}

class SActiveRecord extends SObservable implements ArrayAccess
{
    public $errors        = array();
    public $attr_required  = array();
    public $attr_protected = array();
    public $attr_unique    = array();
    public $validations   = array();
    
    public $record_timestamps = False;
    
    public static $table_name_prefix = null;
    public static $table_name_suffix = null;
    
    protected static $conn = null;
    protected $values      = array();
    protected $meta        = null;
    
    public function __construct($values = null)
    {
        $this->meta = SActiveRecordMeta::retrieve(get_class($this));
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
                if (!is_object($value) && $value !== null) $this->$key = stripslashes($value);
                else $this->$key = $value;
            }
            if (!$this->attr_exists($key)) $this->values[$key] = $value;
        }
        
        if (!empty($multi_params_attributes)) $this->assign_multiparams_attributes($multi_params_attributes);
    }
    
    public function save()
    {
        if (!$this->is_valid()) return false;
        $this->set_state('before_save');
        if ($this->is_new_record()) $this->create_record();
        else $this->update_record();
        $this->set_state('after_save');
        return true;
    }
    
    public function delete()
    {
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
                                             WHERE {$this->meta->identity_field}=$id LIMIT 1");
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
    
    protected function attr_exists($name)
    {
        return array_key_exists($name, $this->meta->attributes);
    }
    
    protected function read_attribute($name)
    {
        if (!$this->attr_exists($name)) return;
        if (!isset($this->values[$name]))
            $this->values[$name] = $this->meta->attributes[$name]->default_value($this);
        
        return $this->values[$name];
    }
    
    protected function write_attribute($name, $value)
    {
        if (!$this->attr_exists($name)) return;
        $this->values[$name] = $this->meta->attributes[$name]->typecast($this, $value);
    }
    
    protected function read_id()
    {
        return $this->read_attribute($this->meta->identity_field);
    }
    
    protected function write_id($value)
    {
        $this->write_attribute($this->meta->identity_field, $value);
    }
    
    /**
     * Creates string values for all attributes that needs more than one single parameter
     * (such as Dates).
     */
    protected function assign_multiparams_attributes($params)
    {
        $errors = array();
        foreach($params as $key => $value)
        {
            $type = $this->meta->attributes[$key]->type;
            switch ($type)
            {
                case 'date':
                    $this->$key = $value['year'].'-'.$value['month'].'-'.$value['day'];
                    break;
                case 'datetime':
                    $this->$key = $value['year'].'-'.$value['month'].'-'.$value['day']
                                  .' '.$value['hour'].':'.$value['min'].':'.$value['sec'];
                    break;
            }
        }
    }
    
    protected function prepare_sql_set()
    {
        $set = array();
        foreach($this->meta->attributes as $column => $attr)
            if (!array_key_exists($column, $this->meta->relationships))
                $set[] = "$column = ".$this->conn()->quote($this->$column, $attr->type);
        
        return 'SET '.join(',', $set);
    }
    
    protected function save_with_timestamps()
    {
        $t = SDateTime::today();
        if ($this->is_new_record())
            if ($this->attr_exists('created_on')) $this->values['created_on'] = $t;
        
        if ($this->attr_exists('updated_on')) $this->values['updated_on'] = $t;
    }
    
    protected function before_create() {}
    
    protected function after_create() {}
    
    protected function before_update() {}
    
    protected function after_update() {}
    
    protected function before_save()
    {
        if ($this->record_timestamps) $this->save_with_timestamps();
        foreach($this->meta->relationships as $k => $v) 
                $this->$k->before_owner_save();
    }
    
    protected function after_save()
    {
        foreach($this->meta->relationships as $k => $v) 
            $this->$k->after_owner_save();
    }
    
    protected function before_delete()
    {
        foreach($this->meta->relationships as $k => $v) 
            $this->$k->before_owner_delete();
    }
    
    protected function after_delete() {}
    
    protected function before_validate() {}
    
    protected function after_validate() {}
    
    protected function run_validations($method)
    {
        foreach (array_keys($this->values) as $key)
            SValidation::validate_attribute($this, $key, $method);
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
    
    private function ensure_proper_type()
    {
        if ($this->meta->descends_from() != 'SActiveRecord')
            $this->write_attribute($this->meta->inheritance_field, SInflection::underscore(get_class($this)));
    }
    
    /**
     * CONNECTION MANAGEMENT ===================================================
     **/
    public static function connection()
    {
        if (!isset(self::$conn)) self::establish_connection();
        return self::$conn;
    }
    
    protected function conn()
    {
        return self::connection();
    }
    
    protected static function establish_connection($config = array())
    {
        $config = include(ROOT_DIR.'/conf/database.php');
        $driver_class = 'S'.$config[APP_MODE]['driver'].'Driver';
        if (!class_exists($driver_class)) 
            throw new SException('Database driver not found !');
        
        self::$conn = new $driver_class($config[APP_MODE]);
        self::$conn->connect();
    }
}

?>
