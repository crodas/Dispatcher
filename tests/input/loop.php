<?php

/** @Filter numeric */
function numeric($req, $name, $value)
{
    return is_numeric($value);
}

/**
 *  @Route("/loop-{numeric}/l-{numeric:a}-{numeric:x}+/loop/{numeric:b}+/bar")
 */
function foobar($req) {
    return __FUNCTION__;
}
