<?php

function strip_html($str)
{
    return convert_to_numeric_entities(strip_tags($str));
}

function convert_to_numeric_entities($str)
{
  return preg_replace('/[^!-%\x27-;=?-~ ]/e', '"&#".ord("$0").chr(59)',
                      html_entity_decode($str));
}

?>
