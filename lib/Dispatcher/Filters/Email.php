<?php

namespace Dispatcher\Filters;

/**
 *  @Filter email
 *  @Builtin
 */
function is_email($variable)
{
    return "filter_var($variable, FILTER_VALIDATE_EMAIL)";
}

