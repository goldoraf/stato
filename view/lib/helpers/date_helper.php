<?php

function date_select($object_name, $method, $object, $options = array())
{
    list($options['prefix'], $value) = default_date_options($object_name, $method, $object);
    if (isset($options['include_blank']) && $options['include_blank'] == True)
        $date = ($value != Null) ? $value : 0; 
    else
        $date = ($value != Null) ? $value : SDate::today(); 
    
    return select_date($date, $options);
}

function date_time_select($object_name, $method, $object, $options = array())
{
    list($options['prefix'], $value) = default_date_options($object_name, $method, $object);
    if (isset($options['include_blank']) && $options['include_blank'] == True)
        $datetime = ($value != Null) ? $value : Null; 
    else
        $datetime = ($value != Null) ? $value : SDateTime::today(); 
    
    return select_date_time($datetime, $options);
}

function time_select($object_name, $method, $object, $options = array())
{
    list($options['prefix'], $value) = default_date_options($object_name, $method, $object);
    if (isset($options['include_blank']) && $options['include_blank'] == True)
        $datetime = ($value != Null) ? $value : Null; 
    else
        $datetime = ($value != Null) ? $value : SDateTime::today(); 
    
    return select_time($datetime, $options);
}

function select_date($date = Null, $options = array())
{
    if ($date == Null) $date = SDate::today();
    $order = (isset($options['order'])) ? $options['order'] : array('year', 'month', 'day');
    $html = '';
    foreach ($order as $param) 
        $html.= call_user_func('select_'.$param, $date, $options);
    
    return $html;
}

function select_date_time($datetime = Null, $options = array())
{
    if ($datetime == Null) $datetime = SDateTime::today();
    $order = (isset($options['order'])) ? $options['order'] : array('year', 'month', 'day', 'hour', 'minute', 'second');
    $html = '';
    foreach ($order as $param)
        $html.= call_user_func('select_'.$param, $datetime, $options);
    
    return $html;
}

function select_time($datetime = Null, $options = array())
{
    if ($datetime == Null) $datetime = SDateTime::today();
    $order = (isset($options['order'])) ? $options['order'] : array('hour', 'minute', 'second');
    $html = '';
    foreach ($order as $param)
        $html.= call_user_func('select_'.$param, $datetime, $options);
    
    return $html;
}

function select_day($date, $options=array())
{
    $day_options = '';
    $selected = is_date_type($date) ? $date->day : $date;
    for ($i = 1; $i <= 31; $i++)
    {
        if ($i == $selected)
            $day_options.= "<option value=\"{$i}\" selected=\"selected\">{$i}</option>\n";
        else
            $day_options.= "<option value=\"{$i}\">{$i}</option>\n";
    }
    if (!isset($options['field_name'])) $options['field_name'] = 'day';
    
    return select_html($options['field_name'], $day_options, $options);
}

function select_month($date, $options=array())
{
    $month_options = '';
    $selected = is_date_type($date) ? $date->month : $date;
    for ($i = 1; $i <= 12; $i++)
    {
        if (isset($options['use_numbers']) && $options['use_numbers'] == True)
            $month = $i;
        elseif (isset($options['use_abbrv']) && $options['use_abbrv'] == True)
            $month = utf8_encode(strftime('%b', mktime(0,0,0,$i,1,2005)));
        else
            $month = utf8_encode(strftime('%B', mktime(0,0,0,$i,1,2005)));
        
        if (isset($options['add_numbers']) && $options['add_numbers'] == True)
            $month = $i.' - '.$month;
            
        if ($i == $selected)
            $month_options.= "<option value=\"{$i}\" selected=\"selected\">{$month}</option>\n";
        else
            $month_options.= "<option value=\"{$i}\">{$month}</option>\n";
    }
    if (!isset($options['field_name'])) $options['field_name'] = 'month';
    
    return select_html($options['field_name'], $month_options, $options);
}

function select_year($date, $options = array())
{
    $year_options = '';
    $today = SDate::today();
    if (!isset($options['start_year'])) $options['start_year'] = $today->year - 5;
    if (!isset($options['end_year'])) $options['end_year'] = $today->year + 5;
    $step = ($options['start_year'] < $options['end_year']) ? 1 : -1;
    $selected = is_date_type($date) ? $date->year : $date;
    for($i = $options['start_year']; $i != $options['end_year'] + $step; $i = $i + $step)
    {
        if ($i == $selected)
            $year_options.= "<option value=\"{$i}\" selected=\"selected\">{$i}</option>\n";
        else
            $year_options.= "<option value=\"{$i}\">{$i}</option>\n";
    }
    if (!isset($options['field_name'])) $options['field_name'] = 'year';
    
    return select_html($options['field_name'], $year_options, $options);
}

function select_second($datetime, $options = array())
{
    $selected = (get_class($datetime) == 'SDateTime') ? $datetime->sec : $datetime;
    $sec_options = numerical_options(0, 59, $selected);
    if (!isset($options['field_name'])) $options['field_name'] = 'sec';
    return select_html($options['field_name'], $sec_options, $options);
}

function select_minute($datetime, $options = array())
{
    $selected = (get_class($datetime) == 'SDateTime') ? $datetime->min : $datetime;
    $step = (isset($options['minute_step'])) ? $options['minute_step'] : 1;
    $min_options = numerical_options(0, 59, $selected, $step);
    if (!isset($options['field_name'])) $options['field_name'] = 'min';
    return select_html($options['field_name'], $min_options, $options);
}

function select_hour($datetime, $options = array())
{
    $selected = (get_class($datetime) == 'SDateTime') ? $datetime->hour : $datetime;
    $hour_options = numerical_options(0, 23, $selected);
    if (!isset($options['field_name'])) $options['field_name'] = 'hour';
    return select_html($options['field_name'], $hour_options, $options);
}

function select_html($type, $date_options, $options = array())
{
    $html_options = array();
    if (!isset($options['prefix'])) $options['prefix'] = 'date';
    $html_options['name'] = $options['prefix']."[{$type}]";
    if (isset($options['disabled']) && $options['disabled'] == true) $html_options['disabled'] = true;
    if (isset($options['class'])) $html_options['class'] = $options['class'];    
    if (isset($options['include_blank']) && $options['include_blank'] == true) 
        $date_options = '<option value=""></option>'.$date_options;
    
    return content_tag('select', $date_options, $html_options)."\n";
}

function default_date_options($object_name, $method, $object)
{
    return array("{$object_name}[{$method}]", $object->$method);
}

function numerical_options($start, $end, $selected = Null, $step=1)
{
    $options = '';
    for($i = $start; $i <= $end; $i = $i + $step)
    {
        $value = ($i > 9 ? $i : "0{$i}");
        if ($selected == $value)
            $options.= "<option value=\"{$value}\" selected=\"selected\">{$value}</option>\n";
        else
            $options.= "<option value=\"{$value}\">{$value}</option>\n";
    }
    return $options;
}

function is_date_type($date)
{
    return in_array(get_class($date), array('SDate', 'SDateTime'));
}

?>
