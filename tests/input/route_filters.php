<?php

/** @preRoute checkSession @Cache 30 */
function CheckSession(Alltest\Request $req)
{
    $req->set('session', true);
    return !$req->get('fail_session');
}

