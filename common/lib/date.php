<?php

class SDateException extends Exception {}
class SDateConstructException extends Exception {}
class SDateParsingException extends Exception {}

class SDate
{
    protected $attributes = array();
    
    private static $formats = array
    (
        '%Y-%m-%d', '%Y%m%d', '%m/%d/%y', '%m/%d/%Y'
    );
    
    public function __construct($year, $month, $day)
    {
        $ts = mktime(0, 0, 0, $month, $day, $year);
        if ($ts === false)
            throw new SDateConstructException('Invalid parameters.');
        
        $this->attributes_from_ts($ts);
    }
    
    public function __get($key)
    {
        if (!isset($this->attributes[$key]))
            throw new SDateException("Property $key does not exist.");
        return $this->attributes[$key];
    }
    
    public function __set($key, $value)
    {
        throw new SDateException("Properties are read-only.");
    }
    
    public function is_leap()
    {
        return $this->year % 4 == 0 && ($this->year % 400 == 0 || $this->year % 100 != 0);
    }
    
    public function step($step=1)
    {
        return new SDate($this->year, $this->month, $this->day + $step);
    }
    
    public function modify($string)
    {
        $ts = strtotime($string, $this->ts());
        if ($ts === false)
            throw new SDateException('Unable to modify.');
        
        $this->attributes_from_ts($ts);
        return $this;
    }
    
    public function localize($format = '%x')
    {
        return $this->format($format);
    }
    
    public function __toString()
    {
        return $this->sprintf('%04d-%02d-%02d');
    }
    
    public function to_iso8601()
    {
        return date(DATE_ISO8601, $this->ts());
    }
    
    public function to_rfc822()
    {
        return date(DATE_RFC822, $this->ts());
    }
    
    public function to_atom()
    {
        return date(DATE_ATOM, $this->ts());
    }
    
    public function to_rss()
    {
        return date(DATE_RSS, $this->ts());
    }
    
    public function format($strf)
    {
        if ($this->is_server_windows()) 
            return utf8_encode(strftime($strf, $this->ts()));
        else
            return strftime($strf, $this->ts());
    }
    
    public function sprintf($format)
    {
        return sprintf($format, $this->year, $this->month, $this->day);
    }
    
    public function ts()
    {
        return mktime(0, 0, 0, $this->month, $this->day, $this->year);
    }
    
    public static function today()
    {
        $today = getdate();
        return new SDate($today['year'], $today['mon'], $today['mday']);
    }
    
    public static function parse($string, array $user_formats = null)
    {
        if (is_array($string)) return self::from_array($string);
        
        $formats = is_null($user_formats) ? self::$formats : $user_formats;
        foreach ($formats as $format)
        {
            $bits = strptime($string, $format);
            if ($bits && empty($bits['unparsed']))
                return new SDate(1900+$bits['tm_year'], $bits['tm_mon']+1, $bits['tm_mday']);
        }
        throw new SDateParsingException();
    }
    
    public static function from_array($args)
    {
        if (!is_array($args) || count($args) < 3)
            throw new SDateConstructException('Invalid array parameter.');
        foreach (array('year', 'month', 'day') as $key)
            if (!in_array($key, array_keys($args)))
                throw new SDateConstructException('Invalid array parameter.');
        
        return new SDate($args['year'], $args['month'], $args['day']);
    }
    
    protected function attributes_from_ts($ts)
    {
        $date = getdate($ts);
        $this->attributes = array
        (
            'year'  => $date['year'],
            'month' => $date['mon'],
            'mon'   => $date['mon'],
            'day'   => $date['mday'],
            'yday'  => $date['yday'],
            'mday'  => $date['mday'],
            'wday'  => $date['wday'],
            'week'  => date('W', $ts)
        );
    }
    
    protected function is_server_windows()
    {
        return (strtoupper(substr(PHP_OS, 0, 3) == 'WIN'));
    }
}

class SDateTime extends SDate
{
    protected $offset = 0;
    
    private static $formats = array
    (
        '%Y-%m-%d %H:%M:%S', '%Y%m%dT%H:%M:%S', '%m/%d/%y %H:%M:%S', '%m/%d/%Y %H:%M:%S'
    );
    
    public function __construct($year, $month, $day, $hour = 0, $min = 0, $sec = 0, $offset = 0)
    {
        $ts = mktime($hour, $min, $sec, $month, $day, $year);
        if ($ts === false)
            throw new SDateConstructException('Invalid parameters.');
        
        $this->attributes_from_ts($ts);
        $this->offset = $offset;
    }
    
    public function step($step=1)
    {
        return new SDateTime($this->year, $this->month, $this->day + $step, $this->hour,
                             $this->min, $this->sec, $this->offset);
    }
    
    public function new_offset($offset)
    {
        return new SDateTime($this->year, $this->month, $this->day, $this->hour,
                             $this->min, $this->sec, $offset);
    }
    
    public function to_utc()
    {
        return new SDateTime($this->year, $this->month, $this->day, 
                             $this->hour + ($this->local_offset() + $this->offset) / 3600, $this->min, $this->sec);
    }
    
    public function to_local()
    {
        return new SDateTime($this->year, $this->month, $this->day, 
                             $this->hour + $this->offset / 3600, $this->min, $this->sec);
    }
    
    public function local_offset()
    {
        return date('Z');
    }
    
    public function localize($format = '%x %X')
    {
        return $this->format($format);
    }
    
    public function __toString()
    {
        return $this->sprintf('%04d-%02d-%02d %02d:%02d:%02d');
    }
    
    public function sprintf($format)
    {
        return sprintf($format, $this->year, $this->month, 
                       $this->day, $this->hour, $this->min, $this->sec);
    }
    
    public function ts()
    {
        return mktime($this->hour, $this->min, $this->sec, $this->month, $this->day, $this->year);
    }
    
    public static function at($ts)
    {
        $date = getdate($ts);
        return new SDateTime($date['year'], $date['mon'], $date['mday'], 
                             $date['hours'], $date['minutes'], $date['seconds']);
    }
    
    public static function now()
    {
        $today = getdate();
        return new SDateTime($today['year'], $today['mon'], $today['mday'], 
                             $today['hours'], $today['minutes'], $today['seconds']);
    }
    
    public static function today()
    {
        return self::now();
    }
    
    public static function parse($string, array $user_formats = null)
    {
        if (is_array($string)) return self::from_array($string);
        
        $formats = is_null($user_formats) ? self::$formats : $user_formats;
        foreach ($formats as $format)
        {
            $bits = strptime($string, $format);
            if ($bits && empty($bits['unparsed']))
                return new SDateTime(1900+$bits['tm_year'], $bits['tm_mon']+1, $bits['tm_mday'],
                                     $bits['tm_hour'], $bits['tm_min'], $bits['tm_sec']);
        }
        throw new SDateParsingException();
    }
    
    public static function from_array($args)
    {
        if (!is_array($args) || count($args) < 3)
            throw new SDateConstructException('Invalid array parameter.');
        foreach (array('year', 'month', 'day') as $key)
            if (!in_array($key, array_keys($args)))
                throw new SDateConstructException('Invalid array parameter.');
                
        $hour = 0;
        $min  = 0;
        $sec  = 0;
        
        foreach (array('hour', 'min', 'sec') as $key)
            if (in_array($key, array_keys($args))) $$key = $args[$key];
        
        return new SDateTime($args['year'], $args['month'], $args['day'],
                             $hour, $min, $sec);
    }
    
    protected function attributes_from_ts($ts)
    {
        $date = getdate($ts);
        $this->attributes = array
        (
            'year'  => $date['year'],
            'month' => $date['mon'],
            'mon'   => $date['mon'],
            'day'   => $date['mday'],
            'yday'  => $date['yday'],
            'mday'  => $date['mday'],
            'wday'  => $date['wday'],
            'hour'  => $date['hours'],
            'min'   => $date['minutes'],
            'sec'   => $date['seconds'],
            'week'  => date('W', $ts)
        );
    }
}

?>
