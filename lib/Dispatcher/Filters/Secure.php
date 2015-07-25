<?php

namespace Dispatcher\Filters;

/**
 *  @preRoute isSecure
 *  @preRoute Secure
 *  @first
 *  @Builtin
 */
function filter_is_secure($variable)
{
    return "{$variable}->isSecure()";
}
