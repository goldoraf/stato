<?php

namespace Stato\Orm;

function and_()
{
    $expressions = func_get_args();
    if (count($expressions) == 1) return $expressions[0];
    return new ExpressionList($expressions, Operators::AND_);
}

function or_()
{
    $expressions = func_get_args();
    if (count($expressions) == 1) return $expressions[0];
    return new ExpressionList($expressions, Operators::OR_);
}

function not_(ClauseElement $elt)
{
    return $elt->negate();
}