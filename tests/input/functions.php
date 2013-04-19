<?php

/** @Filter __id__ */
function __filter__($req, $name, $value) {
    return $value == "id";
}

/** @Route("/{__id__}") */
function empty_level_1($req) {
    return __FUNCTION__;
}

/** @Route("/") */
function empty_level_2($req) {
    return __FUNCTION__;
}


/** 
 * @Route("/function/{reverse}") 
 * @Route("/function/reverse") 
 * @Route("/ifempty/{something:algo-alias}") 
 */
function some_function($Request)
{
    $phpunit = $Request->get('phpunit');
    $Request->set('controller', __FUNCTION__);
    return __FUNCTION__;
}


/** @Route("/deadly-simple") */
function simple($Request)
{
    $phpunit = $Request->get('phpunit');
    $Request->set('controller', __FUNCTION__);
    return __FUNCTION__;
}

/** @Route("/zzzsfasd_prefix_{id}") */
function soo($req)
{
    return $req->get('id');
}

