<?php
/**
 *  @Route("/something/silly")
 */
class SomeSillyClass
{
    /**
     *  @Error 500
     */
    function handle_500($req, $exception)
    {
        return "Handling exception ". get_class($exception);
    }

    /**
     *  @Route("/error")
     */
    public function xxx()
    {
        throw new \runtimeexception;
    }

    /** @Filter reverse @Cached 3600 */
    function filter_reverse($Req, $name, $value)
    {
        return $name == strrev($value);
    }
 
    /** @Filter something @Cache 3600 */
    function filter_set($Req, $name, $value)
    {
        $Req->attributes->set($name, strtoupper($value));
        return true;
    }

    /**
     *  @preRoute
     */
    function _all_filter($Request, $args)
    {
        $Request->attributes->set('__all__', true);
        return true;
    }

    /**
     *  @postRoute
     */
    function _all_filter_post($Request, $args, $return)
    {
        $Request->attributes->set('__post__', true);
        return $return;
    }
}
