<?php

class SMimeType
{
    private static $set = array();
    private static $lookup = array();
    private static $names_lookup = array();
    
    public $name;
    public $string;
    public $synonyms;
    
    public static function register($string, $name, $string_synonyms = array(), $name_synonyms = array())
    {
        $type = new SMimeType($string, $name, $string_synonyms);
        self::$set[$name] = $type;
        self::$lookup[$string] = $type;
        self::$names_lookup[$name] = $type;
        foreach ($string_synonyms as $s) self::$lookup[$s] = $type;
        foreach ($name_synonyms as $n) self::$names_lookup[$n] = $type;
    }
    
    public static function parse($accept_header)
    {
        $index = 0;
        $accept_list = array();
        $list = explode(',', $accept_header);
        foreach ($list as $l)
        {
            list($string, $q) = explode(';q=', $l);
            $accept_list[] = new SAcceptItem($index++, $string, $q);
        }
        return $accept_list;
    }
    
    public function __construct($string, $name = null, $synonyms = array())
    {
        $this->string = $string;
        $this->name = $name;
        $this->synonyms = $synonyms;
    }
    
    public function __toString()
    {
        return $this->string;
    }
}

class SAcceptItem
{
    public $order;
    public $name;
    public $q;
    
    public function __construct($order, $name, $q = null)
    {
        $this->order = $order;
        $this->name = $name;
        if ($this->name == '*/*') $this->q = 0;
        elseif ($q === null) $this->q = 100;
        else $this->q = (integer) ($q * 100);
    }
}

SMimeType::register("*/*", 'all');
SMimeType::register("text/plain", 'text', array(), array('txt'));
SMimeType::register("text/html", 'html', array('application/xhtml+xml'), array('xhtml'));
SMimeType::register("text/javascript", 'js', array('application/javascript', 'application/x-javascript'));
SMimeType::register("text/css", 'css');
SMimeType::register("text/calendar", 'ics');
SMimeType::register("text/csv", 'csv');
SMimeType::register("application/xml", 'xml', array('text/xml', 'application/x-xml'));
SMimeType::register("application/rss+xml", 'rss');
SMimeType::register("application/atom+xml", 'atom');
SMimeType::register("application/x-yaml", 'yaml', array('text/yaml'));
SMimeType::register("application/json", 'json', array('text/x-json'));

?>
