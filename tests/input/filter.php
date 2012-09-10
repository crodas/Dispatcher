<?php
/** @Filter reverse */
function filter_reverse($Req, $name, $value)
{
    return $name == strrev($value);
}
 
/** @Filter something */
function filter_set($Req, $name, $value)
{
    $Req->set($name, strtoupper($value));
    return true;
}
