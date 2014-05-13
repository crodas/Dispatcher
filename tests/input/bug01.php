<?php

namespace bug01;

/** @Route("/foo/{id}") */
function foobar()
{
    return __FUNCTION__;
}

/** @Route("/foo/bar") */
function barfoo()
{
    return __FUNCTION__;
}

