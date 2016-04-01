<?php

/**
 * @preRoute checkSession
 * @Cache 30
 */
function CheckSession($req)
{
    $req->attributes->set('session', true);
    return !$req->get('fail_session');
}

/**
 * @preRoute run_all
 * @Cache 30 
 */
function CheckSession_another($req)
{
    $req->attributes->set('run_all', true);
    return true;
}

