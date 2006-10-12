<?php

class SDateException extends SException {}
class SDateConstructException extends SException {}
class SDateParsingException extends SException {}

class SDate
{
    protected $attributes = array();
    
    private static $regex = array
    (
        'fr'  => '/^(?P<day>\d{1,2})\/(?P<month>\d{1,2})\/(?P<year>\d{4})$/',
        'iso' => '/^(?P<year>\d{4})-(?P<month>\d{1,2})-(?P<day>\d{1,2})$/'
    );
    
    public function __construct($year, $month, $day)
    {
        $ts = mktime(0, 0, 0, $month, $day, $year);
        if ($ts === false)
            throw new SDateConstructException('Invalid parameters.');
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
    
    public function isLeap()
    {
        return $this->year % 4 == 0 && ($this->year % 400 == 0 || $this->year % 100 != 0);
    }
    
    public function step($step=1)
    {
        return new SDate($this->year, $this->month, $this->day + $step);
    }
    
    public function locale()
    {
        return $this->format(SLocale::translate('FORMAT_DATE'));
    }
    
    public function __toString()
    {
        return $this->sprintf('%04d-%02d-%02d');
    }
    
    public function toIso8601()
    {
        return $this->sprintf('%04d%02d%02dT00:00:00');
    }
    
    public function format($strf)
    {
        return utf8_encode(strftime($strf, $this->ts()));
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
    
    public static function parse($string)
    {
        foreach (self::$regex as $regex)
        {
            if (preg_match($regex, $string, $matches))
                return new SDate($matches['year'], $matches['month'], $matches['day']);
        }
        throw new SDateParsingException();
    }
}

class SDateTime extends SDate
{
    private static $regex = array
    (
        'iso' => '/^(?P<year>\d{4})-(?P<month>\d{1,2})-(?P<day>\d{1,2}) (?P<hour>\d{2}):(?P<min>\d{2}):(?P<sec>\d{2})$/',
        'iso8601' => '/^(?P<year>\d{4})(?P<month>\d{2})(?P<day>\d{2})T(?P<hour>\d{2}):(?P<min>\d{2}):(?P<sec>\d{2})$/'
    );
    
    public function __construct($year, $month, $day, $hour = 0, $min = 0, $sec = 0)
    {
        $ts = mktime($hour, $min, $sec, $month, $day, $year);
        if ($ts === false)
            throw new SDateConstructException('Invalid parameters.');
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
    
    public function locale()
    {
        return $this->format(SLocale::translate('FORMAT_DATETIME'));
    }
    
    public function __toString()
    {
        return $this->sprintf('%04d-%02d-%02d %02d:%02d:%02d');
    }
    
    public function toIso8601()
    {
        return $this->sprintf('%04d%02d%02dT%02d:%02d:%02d');
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
    
    public static function today()
    {
        $today = getdate();
        return new SDateTime($today['year'], $today['mon'], $today['mday'], 
                             $today['hours'], $today['minutes'], $today['seconds']);
    }
    
    public static function parse($string)
    {
        foreach (self::$regex as $regex)
        {
            if (preg_match($regex, $string, $matches))
                return new SDateTime($matches['year'], $matches['month'], $matches['day'], 
                                     $matches['hour'], $matches['min'], $matches['sec']);
        }
        throw new SDateParsingException();
    }
}

?>
