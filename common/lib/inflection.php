<?php

class SInflection
{
    private static $plural_rules = array
    (
        '/(fish)$/i'                => '\1\2',       # fish
        '/(x|ch|ss|sh)$/'			=> '\1es',       # search, switch, fix, box, process, address
		'/series$/'					=> '\1series',
		'/([^aeiouy]|qu)ies$/'	    => '\1y',
		'/([^aeiouy]|qu)y$/'		=> '\1ies',      # query, ability, agency
		'/(?:([^f])fe|([lr])f)$/'   => '\1\2ves',    # half, safe, wife
		'/sis$/'					=> 'ses',        # basis, diagnosis
		'/([ti])um$/'				=> '\1a',        # datum, medium
		'/person$/'					=> 'people',     # person, salesperson
		'/man$/'					=> 'men',        # man, woman, spokesman
		'/child$/'					=> 'children',   # child
		'/s$/'						=> 's',          # no change (compatibility)
		'/$/'                       => 's'
    );
    
    private static $singular_rules = array
    (
        '/(f)ish$/i'               => '\1\2ish',
        '/(x|ch|ss)es$/'		   => '\1',
		'/movies$/'				   => 'movie',
		'/series$/'				   => 'series',
		'/([^aeiouy]|qu)ies$/'     => '\1y',
		'/([lr])ves$/'			   => '\1f',
		'/([^f])ves$/'			   => '\1fe',
		'/(analy|ba|diagno|parenthe|progno|synop|the)ses$/' => '\1sis',
		'/([ti])a$/'				=> '\1um',
		'/people$/'					=> 'person',
		'/men$/'					=> 'man',
		'/status$/'					=> 'status',
		'/children$/'				=> 'child',
		'/news$/'					=> 'news',
		'/s$/'						=> ''
    );
    
    public static function add_singular_rule($regex, $singular)
    {
        self::$singular_rules = array($regex => $singular) + self::$singular_rules;
    }
    
    public static function add_plural_rule($regex, $plural)
    {
        self::$plural_rules = array($regex => $plural) + self::$plural_rules;
    }
    
    public static function pluralize($word)
    {
        foreach (self::$plural_rules as $rule => $replace)
        {
			if (preg_match($rule, $word))
                return preg_replace($rule, $replace, $word);
		}
		return $word;
    }
    
    public static function singularize($word)
    {
        foreach (self::$singular_rules as $rule => $replace)
        {
			if (preg_match($rule, $word))
                return preg_replace($rule, $replace, $word);
		}
		return $word;
    }
    
    public static function underscore($camel_cased_word)
    {
        return strtolower(preg_replace('/([a-z\d])([A-Z])/', '\1_\2', 
            preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $camel_cased_word)));
    }
    
    public static function camelize($underscore_word)
    {
        return preg_replace('/(^|_)(.)/e', "strtoupper('\\2')", $underscore_word);
    }
    
    public static function wikify($sentence)
    {
        return strtolower(preg_replace('/\W/', '', preg_replace('/\s/', '_', $sentence)));
    }
    
    public static function humanize($word)
    {
        return ucfirst(preg_replace('/_/', ' ', preg_replace('/_id/', '', $word)));
    }
}

?>
