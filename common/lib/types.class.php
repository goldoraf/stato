<?php

require_once(ROOT_DIR.'/lib/adodb-time.class.php');

class Date
{
    public $day   = Null;
    public $month = Null;
    public $year  = Null;
    
    private static $regex = array
    (
        'fr'  => '/^(?P<day>\d{1,2})\/(?P<month>\d{1,2})\/(?P<year>\d{4})$/',
        'iso' => '/^(?P<year>\d{4})-(?P<month>\d{1,2})-(?P<day>\d{1,2})$/'
    );
    
    public function __construct($year, $month, $day)
    {
        if ($day == 0 || $month == 0 || $year ==0)
            throw new Exception('Date class constructor needs not null values.');
            
        $this->day   = $day;
        $this->month = $month;
        $this->year  = $year;
    }
    
    public function locale()
    {
        return $this->format(Context::locale('FORMAT_DATE'));
    }
    
    public function __toString()
    {
        return adodb_date("Y-m-d", $this->ts());
    }
    
    public function format($strf)
    {
        //return utf8_encode(adodb_strftime($strf, $this->ts()));
        return adodb_strftime($strf, $this->ts());
    }
    
    public function ts()
    {
        return adodb_mktime(0, 0, 0, $this->month, $this->day, $this->year);
    }
    
    public static function today()
    {
        $today = getdate();
        return new Date($today['year'], $today['mon'], $today['mday']);
    }
    
    public static function parse($string)
    {
        foreach (self::$regex as $regex)
        {
            if (preg_match($regex, $string, $matches))
            {
                return new Date($matches['year'], $matches['month'], $matches['day']);
            }
        }
        return False;
    }
}

class DateTime extends Date
{
    public $hour = Null;
    public $min  = Null;
    public $sec  = Null;
    
    private static $regex = array
    (
        'iso' => '/^(?P<year>\d{4})-(?P<month>\d{1,2})-(?P<day>\d{1,2}) (?P<hour>\d{2}):(?P<min>\d{2}):(?P<sec>\d{2})$/'
    );
    
    public function __construct($year, $month, $day, $hour, $min, $sec)
    {
        parent::__construct($year, $month, $day);
        $this->hour = $hour;
        $this->min  = $min;
        $this->sec  = $sec;
    }
    
    public function locale()
    {
        return $this->format(Context::locale('FORMAT_DATETIME'));
    }
    
    public function __toString()
    {
        return adodb_date("Y-m-d H:i:s", $this->ts());
    }
    
    public function ts()
    {
        return adodb_mktime($this->hour, $this->min, $this->sec, $this->month, $this->day, $this->year);
    }
    
    public static function today()
    {
        $today = getdate();
        return new DateTime($today['year'], $today['mon'], $today['mday'], $today['hours'], $today['minutes'], $today['seconds']);
    }
    
    public static function parse($string)
    {
        foreach (self::$regex as $regex)
        {
            if (preg_match($regex, $string, $matches))
            {
                return new DateTime($matches['year'], $matches['month'], $matches['day'], $matches['hour'], $matches['min'], $matches['sec']);
            }
        }
        return False;
    }
}

?>
