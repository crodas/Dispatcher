<?php

/** @Route("/foobar/12345") */
class SomeMethodController
{
    /**
     * @Method GET
     **/
    public function get($req)
    {
        $req->attributes->set('method', __METHOD__);
        return __METHOD__;
    }

    /** 
     * @Method POST 
     * @Method DELETE
     */
    public function modify($req)
    {
        $req->attributes->set('method', __METHOD__);
        return __METHOD__;
    }

    /**
     * @Route("/something")
     * @Method POST
     * @Method DELETE
     */
    public function modify_something($req)
    {
        $req->attributes->set('method', __METHOD__);
        return __METHOD__;
    }
}
