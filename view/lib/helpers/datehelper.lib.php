<?php

function date_select($object, $method, $options = array())
{
    list($id, $options['prefix'], $value) = default_options($object, $method);
    if (isset($options['include_blank']) && $options['include_blank'] == True)
        $date = ($value != Null) ? $value : 0; 
    else
        $date = ($value != Null) ? $value : SDate::today(); 
    
    $order = (isset($options['order'])) ? $options['order'] : array('year', 'month', 'day');
    $html = '';
    foreach ($order as $param)
    {
        $html.= call_user_func('select_'.$param, $date, $options);
    }
    return $html;
}

function date_time_select($object, $method, $options = array())
{
    list($id, $options['prefix'], $value) = default_options($object, $method);
    if (isset($options['include_blank']) && $options['include_blank'] == True)
        $datetime = ($value != Null) ? $value : Null; 
    else
        $datetime = ($value != Null) ? $value : SDateTime::today(); 
    
    $order = (isset($options['order'])) ? $options['order'] : array('year', 'month', 'day', 'hour', 'minute', 'second');
    $html = '';
    foreach ($order as $param)
    {
        $html.= call_user_func('select_'.$param, $date, $options);
    }
    return $html;
}

function time_select($object, $method, $options = array())
{
    list($id, $options['prefix'], $value) = default_options($object, $method);
    if (isset($options['include_blank']) && $options['include_blank'] == True)
        $datetime = ($value != Null) ? $value : Null; 
    else
        $datetime = ($value != Null) ? $value : SDateTime::today(); 
    
    $order = (isset($options['order'])) ? $options['order'] : array('hour', 'minute', 'second');
    $html = '';
    foreach ($order as $param)
    {
        $html.= call_user_func('select_'.$param, $date, $options);
    }
    return $html;
}

function select_date($date = Null, $options = array())
{
    if ($date == Null) $date = SDate::today();
    return select_day($date, $options).select_month($date, $options).select_year($date, $options);
}

function select_date_time($datetime = Null, $options = array())
{
    if ($datetime == Null) $datetime = SDateTime::today();
    return select_day($datetime, $options).select_month($datetime, $options).select_year($datetime, $options)
    .select_hour($datetime, $options).select_minute($datetime, $options).select_second($datetime, $options);
}

function select_time($datetime = Null, $options = array())
{
    if ($datetime == Null) $datetime = SDateTime::today();
    return select_hour($datetime, $options).select_minute($datetime, $options).select_second($datetime, $options);
}

function select_day($date, $options=array())
{
    $dayOptions = '';
    $selected = is_date_type($date) ? $date->day : $date;
    for ($i = 1; $i <= 31; $i++)
    {
        if ($i == $selected)
            $dayOptions.= "<option value=\"{$i}\" selected=\"selected\">{$i}</option>\n";
        else
            $dayOptions.= "<option value=\"{$i}\">{$i}</option>\n";
    }
    if (!isset($options['fieldname'])) $options['fieldname'] = 'day';
    
    return select_html($options['fieldname'], $dayOptions, $options);
}

function select_month($date, $options=array())
{
    $monthOptions = '';
    $selected = is_date_type($date) ? $date->month : $date;
    for ($i = 1; $i <= 12; $i++)
    {
        if (isset($options['use_numbers']) && $options['use_numbers'] == True)
            $month = $i;
        elseif (isset($options['add_numbers']) && $options['add_numbers'] == True)
            $month = $i.' - '.utf8_encode(strftime('%B', mktime(0,0,0,$i,1,2005)));
        elseif (isset($options['use_abbrv']) && $options['use_abbrv'] == True)
            $month = utf8_encode(strftime('%b', mktime(0,0,0,$i,1,2005)));
        else
            $month = utf8_encode(strftime('%B', mktime(0,0,0,$i,1,2005)));
            
        if ($i == $selected)
            $monthOptions.= "<option value=\"{$i}\" selected=\"selected\">{$month}</option>\n";
        else
            $monthOptions.= "<option value=\"{$i}\">{$month}</option>\n";
    }
    if (!isset($options['fieldname'])) $options['fieldname'] = 'month';
    
    return select_html($options['fieldname'], $monthOptions, $options);
}

function select_year($date, $options = array())
{
    $yearOptions = '';
    $today = SDate::today();
    if (!isset($options['start_year'])) $options['start_year'] = $today->year - 5;
    if (!isset($options['end_year'])) $options['end_year'] = $today->year + 5;
    $step = ($options['start_year'] < $options['end_year']) ? 1 : -1;
    $selected = is_date_type($date) ? $date->year : $date;
    for($i = $options['start_year']; $i != $options['end_year']; $i = $i + $step)
    {
        if ($i == $selected)
            $yearOptions.= "<option value=\"{$i}\" selected=\"selected\">{$i}</option>\n";
        else
            $yearOptions.= "<option value=\"{$i}\">{$i}</option>\n";
    }
    if (!isset($options['fieldname'])) $options['fieldname'] = 'year';
    
    return select_html($options['fieldname'], $yearOptions, $options);
}

function select_second($datetime, $options = array())
{
    $selected = (get_class($datetime) == 'SDateTime') ? $datetime->sec : $datetime;
    $secOptions = numerical_options(0, 59, $selected);
    if (!isset($options['fieldname'])) $options['fieldname'] = 'sec';
    return select_html($options['fieldname'], $secOptions, $options);
}

function select_minute($datetime, $options = array())
{
    $selected = (get_class($datetime) == 'SDateTime') ? $datetime->min : $datetime;
    $step = (isset($options['minute_step'])) ? $options['minute_step'] : 1;
    $minOptions = numerical_options(0, 59, $selected, $step);
    if (!isset($options['fieldname'])) $options['fieldname'] = 'min';
    return select_html($options['fieldname'], $minOptions, $options);
}

function select_hour($datetime, $options = array())
{
    $selected = (get_class($datetime) == 'SDateTime') ? $datetime->hour : $datetime;
    $hourOptions = numerical_options(0, 23, $selected);
    if (!isset($options['fieldname'])) $options['fieldname'] = 'hour';
    return select_html($options['fieldname'], $hourOptions, $options);
}

function select_html($type, $dateOptions, $options = array())
{
    if (!isset($options['prefix'])) $options['prefix'] = 'date';
    $html = '<select name="'.$options['prefix']."[{$type}]\">\n";
    if (isset($options['include_blank']) && $options['include_blank'] == True) $html.= '<option value=""></option>';
    $html.= $dateOptions."</select>\n";
    return $html;
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
