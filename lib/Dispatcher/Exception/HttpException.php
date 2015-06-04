<?php

namespace Dispatcher\Exception;

use Symfony\Component\HttpFoundation\Request;
use Exception;

class HttpException extends Exception
{
    public $errno;
    public $req;

    public function __construct(Request $req, $errno, $msg = '')
    {
        $this->errno = $errno;
        $this->req   = $req;
        parent::__construct($msg);
    }
}
