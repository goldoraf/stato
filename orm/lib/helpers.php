<?php

function and_()
{
    $expressions = func_get_args();
    if (count($expressions) == 1) return $expressions[0];
    return new Stato_ExpressionList($expressions, Stato_Operators::AND_);
}

function or_()
{
    $expressions = func_get_args();
    if (count($expressions) == 1) return $expressions[0];
    return new Stato_ExpressionList($expressions, Stato_Operators::OR_);
}

function not_(Stato_ClauseElement $elt)
{
    return $elt->negate();
}