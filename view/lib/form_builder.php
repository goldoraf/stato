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
    private $objectName = null;
    private $object     = null;
    private $options    = array();
    
    public function __construct($objectName, $object, $options = array(), $decorator = null)
    {
        $this->objectName = $objectName;
        $this->object = $object;
        $this->options = $options;
    }
    
    public function label($method, $text = null, $options = array())
    {
        if ($text === null) $text = ucfirst($method);
        if (isset($this->options['index']))
            $id = $this->objectName.'_'.$this->options['index']."_{$method}";
        else
            $id = $this->objectName."_{$method}";
        return content_tag('label', $text, array_merge(array('for' => $id), $options));
    }
    
    public function text_field($method, $options = array())
    {
        return text_field($this->objectName, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function file_field($method, $options = array())
    {
        return file_field($this->objectName, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function password_field($method, $options = array())
    {
        return password_field($this->objectName, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function hidden_field($method, $options = array())
    {
        return hidden_field($this->objectName, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function text_area($method, $options = array())
    {
        return text_area($this->objectName, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function check_box($method, $options = array(), $checkedValue = '1', $uncheckedValue = '0', $boolean = true)
    {
        return check_box($this->objectName, $method, $this->object, array_merge($this->options, $options), $checkedValue, $uncheckedValue, $boolean);
    }
    
    public function radio_button($method, $tagValue, $options = array())
    {
        return radio_button($this->objectName, $method, $this->object, $tagValue, array_merge($this->options, $options));
    }
    
    public function select($method, $choices, $options = array(), $htmlOptions = array())
    {
        return select($this->objectName, $method, $this->object, $choices, $options, array_merge($this->options, $htmlOptions));
    }
    
    public function collection_select($method, $collection, $valueProp='id', $textProp=null, $options=array(), $htmlOptions = array())
    {
        return collection_select($this->objectName, $method, $this->object, $collection, 
                                 $valueProp, $textProp, $options, array_merge($this->options, $htmlOptions));
    }
    
    public function date_select($method, $options = array())
    {
        return date_select($this->objectName, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function date_time_select($method, $options = array())
    {
        return date_time_select($this->objectName, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function time_select($method, $options = array())
    {
        return time_select($this->objectName, $method, $this->object, array_merge($this->options, $options));
    }
    
    public function error_message_for($options = array())
    {
        return error_message_for($this->objectName, $this->object, array_merge($this->options, $options));
    }
    
    public function input($method, $options = array())
    {
        return input($this->objectName, $method, $this->object, array_merge($this->options, $options));
    }
}

?>
