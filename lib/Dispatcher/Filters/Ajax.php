<?php

namespace Dispatcher\Filters;

/**
 *  @preRoute isAjax
 *  @preRoute Ajax
 *  @preRoute isXmlHttpRequest
 *  @preRoute XmlHttpRequest
 *  @first
 *  @builtin
 */
function is_ajax($variable)
{
    return "{$variable}->isXmlHttpRequest()";
}
