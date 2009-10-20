<?php

class SActiveRecordForm extends SForm
{
    protected $class;
    protected $instance;
    protected $meta;
    protected $include;
    protected $exclude;
    
    protected static $column_mapping = array
    (
        'string'   => 'SCharField',
        'text'     => 'STextField',
        'date'     => 'SDateTimeField',
        'datetime' => 'SDateTimeField',
        'integer'  => 'SIntegerField',
        'float'    => 'SFloatField',
        'boolean'  => 'SBooleanField'
    );
    
    protected static $association_mapping = array
    (
        'SBelongsTo'  => 'SCollectionChoiceField',
        'SHasOne'     => 'SCollectionChoiceField',
        'SHasMany'    => 'SCollectionMultipleChoiceField',
        'SManyToMany' => 'SCollectionMultipleChoiceField'
    );
    
    public function __construct(SActiveRecord $instance = null, $data = null, $files = null)
    {
        $this->meta = SMapper::retrieve($this->class);
        
        if ($instance !== null) {
            $this->instance = $instance;
            $this->set_initial_values($this->get_values_from_instance());
        } else {
            $class = $this->class;
            $this->instance = new $class;
        }
        
        $this->instantiate_fields();
        $this->set_prefix(SInflection::underscore($this->class));
        $this->bind($data, $files);
    }
    
    protected function instantiate_fields()
    {
        if (!$this->instance->is_new_record()) {
            $this->id = new SCharField(array('input' => 'SHiddenInput'));
        }
        foreach ($this->meta->content_attributes() as $name => $column) {
            $field_options = array();
            if (array_key_exists($column->type, self::$column_mapping)) {
                $field_class = self::$column_mapping[$column->type];
            } else {
                $field_class = 'SCharField';
                $field_options['input'] = 'SHiddenInput';
            }
            if ($column->type == 'string' && $column->limit !== null) {
                $field_options['max_length'] = $column->limit;
            }
            if ($column->null === false) {
                $field_options['required'] = true;
            }
            $this->{$name} = new $field_class($field_options);
        }
        foreach ($this->meta->associations as $name => $association) {
            $queryset = new SQuerySet(SMapper::retrieve($association->meta->class));
            $field_class = self::$association_mapping[$association->meta->type];
            $this->{$name} = new $field_class($queryset);
        }
    }
    
    protected function get_values_from_instance()
    {
        $values = $this->instance->to_array();
        foreach ($this->meta->associations as $name => $association) {
            $type = $association->meta->type;
            if ($type == 'SBelongsTo' || $type == 'SHasOne') {
                $values[$name] = ($this->instance->{$name}->is_null()) ? null : $this->instance->{$name}->id;
            } else {
                $ids = array();
                foreach ($this->instance->{$name}->all() as $r) $ids[] = $r->id;
                $values[$name] = $ids;
            }
        }
        return $values;
    }
    
    protected function list_fields()
    {
        if (isset($this->include)) return $this->include;
        if (isset($this->exclude)) return array_diff(array_keys($this->fields), $this->exclude);
        return array_keys($this->fields);
    }
    
    protected function validate_uniqueness($field, $scope = null)
    {
        $value = $this->data->$field;
        $qs = new SQuerySet($this->meta);
        $filter = $field . ($value === null) ? ' IS ?' : ' = ?';
        $qs = $qs->filter($filter, array($value));
        
        if ($scope !== null) {
            $scope_value = $this->data->$scope;
            $filter = $scope . ($scope_value === null) ? ' IS ?' : ' = ?';
            $qs = $qs->filter($filter, array($scope_value));
        }
        
        if (!$this->data->is_new_record())
            $qs = $qs->filter($this->meta->identity_field.' <> ?', array($this->data->id));
             
        if ($qs->count() != 0)
            throw new SValidationError($this->error_messages['unique']);
    }
}

class SCollectionChoiceField extends SChoiceField
{
    protected $queryset;
    protected $value_prop;
    protected $text_prop;
    protected $default_options = array(
        'value_property' => 'id',
        'text_property' => null,
    );
    
    public function __construct(SQuerySet $queryset = null, array $options = array())
    {
        SField::__construct($options);
        $this->queryset = $queryset;
        $this->value_prop = $this->options['value_property'];
        $this->text_prop = $this->options['text_property'];
        $this->fetch_choices();
    }
    
    public function clean($value)
    {
        $value = parent::clean($value);
        if ($this->is_empty($value)) return null;
        
        try {
            $value = $this->queryset->get($value);
        } catch (SRecordNotFound $e) {
            throw new SValidationError($this->error_messages['invalid_choice'], array($value));
        }
            
        return $value;
    }
    
    protected function fetch_choices()
    {
        $this->choices = array();
        foreach ($this->queryset as $record) {
            if ($this->text_prop !== null) {
                $this->choices[$record->{$this->value_prop}] = $record->{$this->text_prop};
            } else {
                if (method_exists($record, '__repr'))
                    $this->choices[$record->{$this->value_prop}] = $record->__repr();
                else
                    $this->choices[$record->{$this->value_prop}] = $record->__toString();
            }
        }
    }
}

class SCollectionMultipleChoiceField extends SCollectionChoiceField
{
    protected $input = 'SMultipleSelect';
    
    public function clean($value)
    {
        if ($this->required && $this->is_empty($value))
            throw new SValidationError($this->error_messages['required']);
            
        if ($this->is_empty($value)) return array();
        
        if (!is_array($value))
            throw new SValidationError($this->error_messages['invalid_list']);
        
        $set = $this->queryset->in_bulk($value);
        $diff = array_diff($value, array_keys($set));
        if (!empty($diff))
            throw new SValidationError($this->error_messages['invalid_choice']);
            
        return $set;
    }
}