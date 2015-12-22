<?php
/*
  +---------------------------------------------------------------------------------+
  | Copyright (c) 2014 César Rodas                                                  |
  +---------------------------------------------------------------------------------+
  | Redistribution and use in source and binary forms, with or without              |
  | modification, are permitted provided that the following conditions are met:     |
  | 1. Redistributions of source code must retain the above copyright               |
  |    notice, this list of conditions and the following disclaimer.                |
  |                                                                                 |
  | 2. Redistributions in binary form must reproduce the above copyright            |
  |    notice, this list of conditions and the following disclaimer in the          |
  |    documentation and/or other materials provided with the distribution.         |
  |                                                                                 |
  | 3. All advertising materials mentioning features or use of this software        |
  |    must display the following acknowledgement:                                  |
  |    This product includes software developed by César D. Rodas.                  |
  |                                                                                 |
  | 4. Neither the name of the César D. Rodas nor the                               |
  |    names of its contributors may be used to endorse or promote products         |
  |    derived from this software without specific prior written permission.        |
  |                                                                                 |
  | THIS SOFTWARE IS PROVIDED BY CÉSAR D. RODAS ''AS IS'' AND ANY                   |
  | EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED       |
  | WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE          |
  | DISCLAIMED. IN NO EVENT SHALL CÉSAR D. RODAS BE LIABLE FOR ANY                  |
  | DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES      |
  | (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;    |
  | LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND     |
  | ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT      |
  | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS   |
  | SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE                     |
  +---------------------------------------------------------------------------------+
  | Authors: César Rodas <crodas@php.net>                                           |
  +---------------------------------------------------------------------------------+
*/

namespace Dispatcher;

use Symfony\Component\HttpFoundation\Request;

class Router extends Generator
{
    protected $development = false;
    protected $loaded;
    protected static $_router = array();

    public function __construct($output = '')
    {
        if (defined('DEVELOPMENT_MODE')) {
            $this->development = true;
        }
        $this->setOutput($output);
    }

    public function development()
    {
        $this->development = true;
        return $this;
    }

    public function setApplication($name)
    {
        $this->load()->setApplication($name);
        return $this;
    }

    public function url($name, $args = null)
    {
        if (!is_array($args)) {
            $args = func_get_args();
            array_shift($args);
        }
        return $this->load()->getRoute($name, $args);
    }

    public function getRoute($name, $args = null)
    {
        if (!is_array($args)) {
            $args = func_get_args();
            array_shift($args);
        }
        return $this->url($name, $args);
    }
    
    public function load()
    {
        $output = $this->getOutput();

        if (empty(self::$_router[$output])) {
            if (!is_file($output) || $this->development) {
                $this->generate();
            }

            $router = require $output;
            if (!is_object($router)) {
                unlink($output);
                return $this->load();
            }
            self::$_router[$output] = $router;;
        }

        return self::$_router[$output];
    }

    protected $cache;

    public function setCache(FilterCache $cache)
    {
        $this->cache = $cache;
    }

    protected function array_diff($orig, $new)
    {
        $changes = array();
        foreach ($new as $key => $value) {
            if (empty($orig[$key]) || $value !== $orig[$key]) {
                $changes[$key] = $value;
            }
        }
        return $changes;
    }

    // doCachedFilter {{{
    /**
     *  Cache layer for Filters.
     *
     *  If a filter is cachable and a cache object is setup this method will
     *  cache the output of a filter (and all their modifications to a request).
     *
     *  This function is designed to help with those expensive filters which 
     *  for instance talks to databases.
     */
    public function doCachedFilter($callback, Request $req, $key, $value, $ttl)
    {
        if (empty($this->cache)) {
            // no cache layer, we're just a proxy, call to the original callback
            if (is_string($callback)) {
                $return = $callback($req, $key, $value);
            } else {
                $return = $callback[0]->{$callback[1]}($req, $key, $value);
            }
            return $return;
        }

        $objid = "{$key}\0{$value}";
        if ($v=$this->cache->get($objid)) {
            $req->attributes->set('filter:cached:' . $key, true);
            $object = unserialize($v);
            $req->attributes->add($object['set']);
            return $object['return'];
        }

        // not yet cached yet so we call the filter as normal
        // but we save all their changes it does on Request object
        $attributes = $req->attributes->all();
        if (is_string($callback)) {
            $return = $callback($req, $key, $value);
        } else {
            $return = $callback[0]->{$callback[1]}($req, $key, $value);
        }
        if (!$req->attributes->has($key)) {
            $req->attribute->set($key, $value);
        }
        $set = $this->array_diff($attributes,$req->attributes->all());

        $this->cache->set($objid, serialize(compact('return', 'set')), 3600);

        
        return $return;
    }
    // }}}

    public function doRoute($req = null)
    {
        if (empty($req)) {
            $req = Request::createFromGlobals();
        }

        try {
            $dispatcher = $this->load()->setWrapper($this);
            return $dispatcher->doRoute($req);
        } catch (Exception\HttpException $e) {
            return $dispatcher->handleError($req, $e);
        } catch (\Exception $e) {
            // handle every exception, show it as an internal error (http-500)
            $e->errno = 500;
            return $dispatcher->handleError($req, $e);
        }
    }
}
