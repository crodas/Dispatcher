<?php
/**
 *  @Route("/something/silly")
 */
class SomeSillyClass
{
    /** @Filter reverse @Cached 3600 */
    function filter_reverse($Req, $name, $value)
    {
        return $name == strrev($value);
    }
 
    /** @Filter something @Cache 3600 */
    function filter_set($Req, $name, $value)
    {
        $Req->set($name, strtoupper($value));
        return true;
    }

}
