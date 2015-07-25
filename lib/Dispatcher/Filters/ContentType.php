<?php

namespace Dispatcher\Filters;

/**
 *  @preRoute ContentType
 *  @first
 *  @builtin
 */
function filter_by_content_type($variable, Array $args)
{
    $expected = var_export(current($args) ?: '', true);
    return "{$variable}->getContentType() == {$expected}";
}
