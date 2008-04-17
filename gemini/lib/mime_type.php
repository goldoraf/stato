<?php

class SMimeType
{
    private static $set = array();
    private static $lookup = array();
    private static $names_lookup = array();
    
    public $name;
    public $string;
    public $synonyms;
    
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
    
    public static function register($string, $name, $string_synonyms = array(), $name_synonyms = array())
    {
        $type = new SMimeType($string, $name, $string_synonyms);
        self::$set[$name] = $type;
        self::$lookup[$string] = $type;
        self::$names_lookup[$name] = $type;
        foreach ($string_synonyms as $s) self::$lookup[$s] = $type;
        foreach ($name_synonyms as $n) self::$names_lookup[$n] = $type;
    }
    
    public static function lookup($string)
    {
        return @self::$lookup[$string];   
    }
    
    public static function lookup_by_extension($string)
    {
        return @self::$names_lookup[$string];   
    }
    
    public static function parse($accept_header)
    {
        $index = 0;
        $accept_list = array();
        $list = explode(',', $accept_header);
        foreach ($list as $l)
        {
            @list($string, $q) = preg_split('/;\s*q=/', $l);
            $item = new SAcceptItem($index++, $string, $q);
            $inserted = false;
            foreach ($accept_list as $k => $i)
            {
                if ($i->q < $item->q)
                {
                    $tmp = array_slice($accept_list, $k);
                    array_unshift($tmp, $item);
                    array_splice($accept_list, $k, count($accept_list), $tmp);
                    $inserted = true;
                    break;
                }
            }
            if (!$inserted) $accept_list[] = $item;
        }
        
        if (count($accept_list) > 1)
            $accept_list = self::reduce_xml_entries($accept_list);
        
        $types = array();
        foreach ($accept_list as $item)
        {
            $type = self::lookup($item->name);
            if ($type && !in_array($type->name, $types)) $types[] = $type->name;
        }
        return $types;
    }
    
    private static function reduce_xml_entries($accept_list)
    {
        // Take care of the broken text/xml entry by renaming or deleting it
        $text_xml = self::index_for_name('text/xml', $accept_list);
        $app_xml = self::index_for_name('application/xml', $accept_list);
        
        if ($text_xml !== false && $app_xml !== false)
        {
            if ($accept_list[$text_xml]->q > $accept_list[$app_xml]->q)
                $accept_list[$app_xml]->q = $accept_list[$text_xml]->q;
            if ($app_xml > $text_xml)
            {
                $accept_list[$text_xml] = $accept_list[$app_xml];
                array_splice($accept_list, $app_xml, 1);
                $app_xml = $text_xml;
            }
            else array_splice($accept_list, $text_xml, 1);
        }
        elseif ($text_xml !== false) $accept_list[$text_xml]->name = 'application/xml';
        
        // Look for more specific xml-based types and sort them ahead of app/xml
        if ($app_xml !== false)
        {
            $index = $app_xml;
            $app_xml_type = $accept_list[$app_xml];
            
            while ($index < count($accept_list))
            {
                $type = $accept_list[$index];
                //if ($type->q < $app_xml_type->q) break;
                if (preg_match('/\+xml$/', $type->name))
                {
                    $accept_list[$index] = $accept_list[$app_xml];
                    $accept_list[$app_xml] = $type;
                    $app_xml = $index;
                }
                $index++;   
            }
        }
        return $accept_list;
    }
    
    private static function index_for_name($name, $list)
    {
        foreach ($list as $k => $v)
            if ($v->name == $name) return $k;
        return false;
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
