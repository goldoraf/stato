<?php
/**
 * Date form helpers
 * 
 * Set of functions for creating select/option tags for different kinds of date elements.
 * Common options:
 * <ul> 
 * <li><var>prefix</var> - overwrites the default prefix of "date" used for the select names. 
 * So specifying "birthday" would give birthday[month] instead of date[month] if passed to the select_month method.</li>
 * <li><var>include_blank</var> - set to true if it should be possible to set an empty date.</li>
 * </ul>
 * @package Stato
 * @subpackage view
 */
/**
/**
 * Returns a set of select tags (one for year, month, and day) pre-selected for 
 * accessing a specified date-based attribute (identified by <var>$method</var>) on an object 
 * assigned to the template (identified by <var>$object</var>). 
 * 
 * It's possible to tailor the selects through the <var>$options</var> array.
 * 
 * Examples :
 * <code>
 * date_select('post', 'written_on');
 * date_select('post', 'written_on', array('start_year' => 2005));
 * date_select('post', 'written_on', array('start_year' => 2005, 'use_months_number' => true, 'include_blank' => true));
 * date_select('post', 'written_on', array('order' => array('year', 'month', 'day'));
 * </code> 
 */
function date_select($object_name, $method, $object, $options = array())
{
    list($options['prefix'], $value) = default_date_options($object_name, $method, $object);
    if (isset($options['include_blank']) && $options['include_blank'] == True)
        $date = ($value != Null) ? $value : 0; 
    else
        $date = ($value != Null) ? $value : SDate::today(); 
    
    return select_date($date, $options);
}

/**
 * Returns a set of select tags (one for year, month, day, hour, and minute) 
 * pre-selected for accessing a specified datetime-based attribute (identified 
 * by <var>$method</var>) on an object assigned to the template (identified by <var>$object</var>).
 */
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

/**
 * Returns a set of select tags (one for year, month, and day) pre-selected with the <var>$date</var>.
 *  
 * You can explicitly set the order of the tags using the <var>order</var> option with an array 
 * of strings <var>year</var>, <var>month</var> and <var>day</var> in the desired order.
 */
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

/**
 * Returns a select tag with options for each of the days 1 through 31 with the current day selected.
 *  
 * <var>$date</var> can be a SDate instance or a day number. You can also override the field name 
 * using the <var>field_name</var> option ('day' by default).
 */
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

/**
 * Returns a select tag with options for each of the months with the current month selected.
 *  
 * <var>$date</var> can be a SDate instance or a month number. You can override the field name 
 * using the <var>field_name</var> option ('day' by default). <var>use_numbers</var>,
 * <var>use_abbrv</var>, <var>add_numbers</var> options are also available.
 * 
 * Examples:
 * <code>
 * select_month(SDate::today());                               // "January", "February", ...
 * select_month(SDate::today(), array('use_numbers' => true)); // "1", "2", ...
 * select_month(SDate::today(), array('add_numbers' => true)); // "1 - January", "2 - February", ...
 * select_month(SDate::today(), array('use_abbrv' => true));   // "Jan", "Feb", ...
 * </code>      
 */
function select_month($date, $options=array())
{
    $month_options = '';
    $selected = is_date_type($date) ? $date->month : $date;
    for ($i = 1; $i <= 12; $i++)
    {
        if (isset($options['use_numbers']) && $options['use_numbers'] == True)
            $month = $i;
        elseif (isset($options['use_abbrv']) && $options['use_abbrv'] == True)
            $month = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? utf8_encode(strftime('%b', mktime(0,0,0,$i,1,2005)))
                                                                 : strftime('%b', mktime(0,0,0,$i,1,2005));
        else
            $month = (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') ? utf8_encode(strftime('%B', mktime(0,0,0,$i,1,2005)))
                                                                 : strftime('%B', mktime(0,0,0,$i,1,2005));
        
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

/**
 * Returns a select tag with options for each of the five years on each side of the current, which is selected. 
 * 
 * The five year radius can be changed using the <var>start_year</var> and <var>end_year</var> options.
 * Both ascending and descending year lists are supported by making <var>start_year</var> less than or 
 * greater than <var>end_year</var>. <var>$date</var> can be a SDate instance or a year number.   
 */
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

/**
 * Returns a select tag with options for each of the minutes 0 through 59 with the current minute selected. 
 * 
 * <var>$datetime</var> can be a SDateTime instance or a minute number. You can also
 * specify a <var>minute_step</var> option. 
 */
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

/**
 * @ignore
 */
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

/**
 * @ignore
 */
function default_date_options($object_name, $method, $object)
{
    return array("{$object_name}[{$method}]", $object->$method);
}

/**
 * @ignore
 */
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

/**
 * @ignore
 */
function is_date_type($date)
{
    return in_array(get_class($date), array('SDate', 'SDateTime'));
}

?>
