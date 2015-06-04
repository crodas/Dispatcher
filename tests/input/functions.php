<?php

/** @Filter __id__ */
function __filter__($req, $name, $value) {
    return $value == "id";
}

/** @Route("/{__id__}", route_with_id) */
function empty_level_1($req) {
    return __FUNCTION__;
}

/** @Route("/just-ajax-2") @XmlHttpRequest @ContentType json  */
function is_ajax_json() {
    return __FUNCTION__;
}

/** @Route("/just-ajax") @XmlHttpRequest */
function is_ajax() {
    return __FUNCTION__;
}

/** @Route("/") */
function empty_level_2($req) {
    return __FUNCTION__;
}

/**
 *  @Route("/int/{int:a}/{int:b}")
 */
function x_int($req, $a, $b)
{
    return __FUNCTION__;
}

/**
 *  @Filter MongoId2
 *  @Builtin
 */
function filter_built_in($var)
{
    return "preg_match('@^[a-f0-9]{24}$@', $var)";
}

/**
 *  @Route("/{mongoid2}")
 */
function mongoid_controller($req, $mongoid2)
{
    return __FUNCTION__;
}

/**
 *  @Route("/{email}")
 */
function email_controller($req)
{
    return __FUNCTION__;
}

/**
 *  @Route("/numeric/{number:a}/{number:b}")
 */
function numbers($req, $a, $b)
{
    return __FUNCTION__;
}


/** 
 * @Route("/function/{reverse}") 
 * @Route("/function/reverse") 
 * @Route("/ifempty/{something:algo-alias}") 
 */
function some_function($Request)
{
    $phpunit = $Request->attributes->get('phpunit');
    $Request->attributes->set('controller',  __FUNCTION__);
    return __FUNCTION__;
}


/** @Route("/deadly-simple") */
function simple($Request)
{
    $phpunit = $Request->attributes->get('phpunit');
    $Request->attributes->set('controller',  __FUNCTION__);
    return __FUNCTION__;
}

/** @Route("/zzzsfasd_prefix_{id}") */
function soo($req)
{
    return $req->attributes->get('id');
}

