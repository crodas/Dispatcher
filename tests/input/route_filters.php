<?php

/** @preRoute checkSession @Cache 30 */
function CheckSession(Alltest\Request $req)
{
    $req->set('session', true);
    return !$req->get('fail_session');
}

/** @preRoute run_all @Cache 30 */
function CheckSession_another(Alltest\Request $req)
{
    $req->set('run_all', true);
    return true;
}

