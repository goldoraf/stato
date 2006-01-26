<?php

function html_escape($html)
{
    return htmlspecialchars($html, ENT_QUOTES);
}

function truncate($text, $length = 30, $truncateString = '...')
{
    if (strlen($text) > $length)
        return substr_replace($text, $truncateString, $length - strlen($truncateString));
    else
        return $text;
}

?>
