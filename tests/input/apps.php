<?php

/**
 *  @Route("/prefix1/apps")
 *  @App foo
 *  @App bar
 */
class fooApp
{
    /**
     *  @Route("/foo")
     */
    public function f()
    {
        return 'foobar';
    }
}
