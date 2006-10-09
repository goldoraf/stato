<?php

function apply_behaviour($selector, $behaviour)
{
    return javascript_tag("Event.addBehavior({ '{$selector}' : function(e) { {$behaviour} } });");
}

?>
