<?php

/** @Filter numeric */
function numeric($req, $name, $value)
{
    return is_numeric($value);
}

/**
 *  @Route("/{foobar_nofilter}+/route")
 *  @Route("/router/{foobar_nofilter}+")
 *  @Route("/routex/{foobar_nofilter}+/all")
 */
function yet_another($req) {
    return __FUNCTION__;
}

/**
 *  @Route("/loop-{numeric}/l-{numeric:a}-{numeric:x}+/loop/{numeric:b}+/bar")
 */
function foobar($req) {
    return __FUNCTION__;
}
