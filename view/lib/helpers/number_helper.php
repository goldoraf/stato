<?php

function number_with_delimiter($number, $delimiter=',')
{
    return preg_replace('/(\d)(?=(\d\d\d)+(?!\d))/', "\\1{$delimiter}", $number);
}

function number_with_precision($number, $precision=3)
{
    return sprintf("%01.{$precision}f", $number);
}

?>
