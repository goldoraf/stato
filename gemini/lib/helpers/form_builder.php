<?php

/**
 * Creates a scope around a specific model object and emulates the API for form helpers.
 * 
 * <code><?= start_form_tag(...); ?>
 *<? $f = new SFormBuilder('post', $this->post); ?>
 *  Title : <?= $f->text_field('title'); ?>
 *  Date :  <?= $f->date_select('written_on'); ?>
 *  Body :  <?= $f->text_area('body'); ?>     
 *<?= end_form_tag(); ?></code>
 * It is a modest increase in comfort, in that instead of <var>text_field('person',
 * $this->person, 'title')</var>, you simply type <var>$f->text_field('title')</var>.
 * @package Stato
 * @subpackage view
 */
class SFormBuilder
{
    private $object_name = null;
    private $object     = null;
    private $options    = array();
    
    public function __construct($object_name, $object, $options = array(), $decorator = null)
    {
        $this->object_name = $object_name;
        $this->object = $object;
        $this->options = $options;
    }
    
    public function label($method, $text = null, $options = array())
    {
        if ($text === null) $text = ucfirst($method);
        if (isset($this->options['index']))
            $id = $this->object_name.'_'.$this->options['index']."_{$method}";
        else
            $id = $this->object_name."_{$method}";
        return content_tag('label', $text, array_merge(array('for' => $id), $options));
    }
    
    public function text_field($method, $options = array())
    {
        return text_field($this->object_name, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function file_field($method, $options = array())
    {
        return file_field($this->object_name, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function password_field($method, $options = array())
    {
        return password_field($this->object_name, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function hidden_field($method, $options = array())
    {
        return hidden_field($this->object_name, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function text_area($method, $options = array())
    {
        return text_area($this->object_name, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function check_box($method, $options = array(), $checked_value = '1', $unchecked_value = '0', $boolean = true)
    {
        return check_box($this->object_name, $method, $this->object, array_merge($this->options, $options), $checked_value, $unchecked_value, $boolean);
    }
    
    public function radio_button($method, $tag_value, $options = array())
    {
        return radio_button($this->object_name, $method, $this->object, $tag_value, array_merge($this->options, $options));
    }
    
    public function select($method, $choices, $options = array(), $html_options = array())
    {
        return select($this->object_name, $method, $this->object, $choices, $options, array_merge($this->options, $html_options));
    }
    
    public function collection_select($method, $collection, $value_prop='id', $text_prop=null, $options=array(), $html_options = array())
    {
        return collection_select($this->object_name, $method, $this->object, $collection, 
                                 $value_prop, $text_prop, $options, array_merge($this->options, $html_options));
    }
    
    public function radio_button_group($method, $choices, $options = array(), $html_options = array())
    {
        return radio_button_group($this->object_name, $method, $this->object, $choices, $options, array_merge($this->options, $html_options));
    }
    
    public function date_select($method, $options = array())
    {
        return date_select($this->object_name, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function date_time_select($method, $options = array())
    {
        return date_time_select($this->object_name, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function time_select($method, $options = array())
    {
        return time_select($this->object_name, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function error_message($options = array())
    {
        return error_message_for($this->object_name, $this->object, array_merge($this->options, $options));
    }
    
    public function error_message_on($method, $options = array())
    {
        return error_message_on($method, $this->object, array_merge($this->options, $options));
    }
    
    public function input($method, $options = array())
    {
        return input($this->object_name, $method, $this->object, array_merge($this->options, $options));
    }
}

?>
