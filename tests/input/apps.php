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

/**
 *  @Route("/")
 *  @App xxx
 */
function home_by_apps($req, $do_fail)
{
    if ($do_fail) {
        throw new \RuntimeException("hi");
    }
    return 'foo-home';
}

