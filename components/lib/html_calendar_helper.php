<?php

class SCalendarHelper
{
    protected $first;
    protected $last;
    protected $options = array
    (
        'table_class'       => 'calendar',
        'day_class'         => 'day',
        'day_name_class'    => 'day-name',
        'day_name_format'   => '%a',
        'month_name_class'  => 'month-name',
        'month_name_format' => '%B %Y',
        'other_month_class' => 'other-month',
        'first_day_of_week' => 0
    );
    
    public function __construct($year, $month, $options = array())
    {
        $this->first = new SDate($year, $month, 1);
        $this->last  = new SDate($year, $month + 1, 0);
        $this->options = array_merge($this->options, $options);
    }
    
    public function render()
    {
        $firstWeekDay = $this->firstDayOfWeek($this->options['first_day_of_week']);
        $lastWeekDay  = $this->lastDayOfWeek($this->options['first_day_of_week']);
        
        $html = '<table class="'.$this->options['table_class'].'"><thead><tr>';
        $html.= '<td colspan="7" class="'.$this->options['month_name_class'].'">'
        .$this->monthNameRowContent($this->first).'</td></tr><tr>';
        
        $d = $this->beginningOfWeek($this->first, $firstWeekDay);
        for ($i = 0; $i <= 6; $i++)
            $html.= '<td class="'.$this->options['day_name_class'].'">'
            .$d->step($i)->format($this->options['day_name_format']).'</td>';
        
        $html.= '</tr><tbody><tr>';
        
        if ($this->first->wday != $firstWeekDay)
        {
            $d = $this->beginningOfWeek($this->first, $firstWeekDay);
            while ($d <= $this->first->step(-1)) {
                $html.= $this->otherMonthCell($d);
                $d = $d->step(1);
            }
        }
        
        $d = $this->first;
        while ($d <= $this->last)
        {
            list($text, $attrs) = $this->textAndAttrs($d);
            $html.= content_tag('td', $text, $attrs);
            if ($d->wday == $lastWeekDay) $html.= '</tr><tr>';
            $d = $d->step(1);
        }
        
        if ($this->last->wday != $lastWeekDay)
        {
            $d = $this->last->step(1);
            $end = $this->beginningOfWeek($this->last->step(7), $firstWeekDay)->step(-1);
            while ($d <= $end) {
                $html.= $this->otherMonthCell($d);
                $d = $d->step(1);
            }
        }
        
        $html.= '</tr></tbody></table>';
        return $html;
    }
    
    public function textAndAttrs($date)
    {
        $attrs = array('class' => $this->options['day_class']);
        if ($this->isWeekEnd($date)) $attrs['class'].= ' week-end-day';
        return array($date->day, $attrs);
    }
    
    public function otherMonthCell($date)
    {
        $html = '<td class="'.$this->options['other_month_class'];
        if ($this->isWeekEnd($date)) $html.= ' week-end-day';
        $html.= '">'.$date->day.'</td>';
        return $html;
    }
    
    public function monthNameRowContent($date)
    {
        return $date->format($this->options['month_name_format']);
    }
    
    protected function firstDayOfWeek($day)
    {
        return $day;
    }
    
    protected function lastDayOfWeek($day)
    {
        if ($day > 0) return $day - 1;
        return 6;
    }
    
    protected function daysBetween($first, $second)
    {
        if ($first > $second) return $second + (7 - $first);
        return $second - $first;
    }
    
    protected function beginningOfWeek($date, $start = 1)
    {
        $daysToBeg = $this->daysBetween($start, $date->wday);
        return $date->step(- $daysToBeg);
    }
    
    protected function isWeekEnd($date)
    {
        return in_array($date->wday, array(0, 6));
    }
}

class WeekCalendarHelper extends SCalendarHelper
{
    protected $current = null;
    protected $events  = array();
    protected $options = array
    (
        'table_class'       => 'week-calendar',
        'day_class'         => 'week-day',
        'day_name_class'    => 'week-day-name',
        'day_name_format'   => '%A',
        'first_day_of_week' => 1
    );
    
    public function __construct($date, $events = array(), $options = array())
    {
        $this->current = $date;
        $this->events = $events;
        $this->options = array_merge($this->options, $options);
    }
    
    public function render()
    {
        $firstWeekDay = $this->firstDayOfWeek($this->options['first_day_of_week']);
        $lastWeekDay  = $this->lastDayOfWeek($this->options['first_day_of_week']);
        
        $d = $this->beginningOfWeek($this->current, $firstWeekDay);
        $html = $this->renderDayTable($d, true);
        for ($i = 1; $i <= 6; $i++) $html.= $this->renderDayTable($d->step($i));
        return $html;
    }
    
    private function renderDayTable($date, $includeHours = false)
    {
        $html = '<table class="'.$this->options['table_class'].'"><thead><tr>';
        if ($includeHours) $html.= '<td />';
        $html.= '<td class="'.$this->options['day_name_class'].'">'
        .$date->format($this->options['day_name_format']).'</td></tr><tbody>';
        
        for ($i = 0; $i <= 31; $i++)
        {
            $d = new SDateTime($date->year, $date->month, $date->day, 8, $i*30, 0);
            if ($i % 2 == 0) $html.= '<tr class="full-hour">';
            else $html.= '<tr class="half-hour">';
            if ($includeHours && $i % 2 == 0) 
                $html.= '<td class="hour-class" rowspan="2">'.$d->format('%H:%M').'</td>';
            $html.= '<td></td></tr>';
        }
        
        $html.= '</tbody></table>';
        return $html;
    }
}

?>
