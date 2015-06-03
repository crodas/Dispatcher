<?php

namespace Dispatcher\Filters;

/**
 *  @Filter numeric
 *  @Filter number
 *  @Builtin
 */
function is_numeric($variable, $name)
{
    return "is_numeric($variable)";
}

/**
 *  @Filter int
 *  @Builtin
 */
function is_int($variable, $name)
{
    return "is_numeric($variable) && (int)$variable === $variable+0";
}

/**
 *  @Filter alphanum
 *  @Filter alnum
 *  @Builtin
 */
function is_alphanum($variable, $name)
{
    return "ctype_alnum($variable)";
}

/**
 *  @Filter alpha
 *  @Builtin
 */
function is_alpha($variable, $name)
{
    return "ctype_alpha($variable)";
}
