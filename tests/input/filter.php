<?php
/** @Filter reverse */
function filter_reverse($Req, $name, $value)
{
    return $name == strrev($value);
}

