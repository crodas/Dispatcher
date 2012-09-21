<?php

/**
 *  @Route("/prefix")
 */
class SomeClass
{
    /** @Route("/some") */
    public function index($Request)
    {
        $Request->set('controller', __METHOD__);
        return __METHOD__;
    }

    /** @Method POST @checkSession */
    public function save()
    {
        return __METHOD__;
    }
}
