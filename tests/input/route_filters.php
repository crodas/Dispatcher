<?php

/** @preRoute checkSession */
function CheckSession(Alltest\Request $req)
{
    $req->set('session', true);
    return !$req->get('fail_session');
}

