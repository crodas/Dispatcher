<?php

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
