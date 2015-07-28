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
 *  @Application xxx
 */
function yet_another_from_app($req, $foobar_nofilter) {
    $phpunit = $req->get('phpunit');
    $phpunit->assertEquals($foobar_nofilter, $req->get('foobar_nofilter'));
    return __FUNCTION__;
}


/**
 *  @Route("/{foobar_nofilter}+/route")
 *  @Route("/router/{foobar_nofilter}+")
 *  @Route("/routex/{foobar_nofilter}+/all")
 */
function yet_another($req, $foobar_nofilter) {
    $phpunit = $req->get('phpunit');
    $phpunit->assertEquals($foobar_nofilter, $req->get('foobar_nofilter'));
    return __FUNCTION__;
}

/**
 *  @Route("/loop-{numeric}/l-{numeric:a}-{numeric:x}+/loop/{numeric:b}+/bar")
 */
function foobar($req) {
    return __FUNCTION__;
}
