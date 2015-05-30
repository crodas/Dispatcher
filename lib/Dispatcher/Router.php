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

class Router extends Generator
{
    protected $development = false;
    protected $loaded;
    protected $_router;

    public function __construct($output = '')
    {
        $this->setOutput($output);
        $this->setNamespace(__CLASS__ . "\\Generated"); 
    }

    public function development()
    {
        $this->development = true;
        return $this;
    }

    public function newRequest()
    {
        $class = $this->getNamespace() . "\\Request";;
        return new $class;
    }

    public function getRoute($name, Array $args = array())
    {
        return $this->load()->getRoute($name, $args);
    }
    
    public function load()
    {
        if ($this->loaded) return $this->_router;

        if (!is_file($this->getOutput()) || $this->development) {
            $this->generate();
        }

        require $this->getOutput();

        $class = $this->getNamespace() . "\\Route";

        $this->loaded  = true;
        $this->_router = new $class; 

        return $this->_router;
    }

    protected $cache;

    public function setCache(FilterCache $cache)
    {
        $this->cache = $cache;
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
    protected function doCachedFilter($callback, Request $req, $key, $value, $ttl)
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

        $objid = "{$key}\n{$value}";
        if ($v=$this->cache->get($objid)) {
            $req->set('filter:cached:' . $key, true);
            $object = unserialize($v);
            foreach ($object['set'] as $key => $value) {
                $req->set($key, $value);
            }
            return $object['return'];
        }

        // not yet cached yet so we call the filter as normal
        // but we save all their changes it does on Request object
        $req->watchChanges();
        if (is_string($callback)) {
            $return = $callback($req, $key, $value);
        } else {
            $return = $callback[0]->{$callback[1]}($req, $key, $value);
        }
        $keys = $req->setIfEmpty($key, $value)->getChanges();
        $set  = array();
        foreach ($keys as $key) {
            $set[$key] = $req->get($key);
        }

        $this->cache->set($objid, serialize(compact('return', 'set')), 3600);

        
        return $return;
    }
    // }}}

    public function doRoute($req = null, Array $server = array())
    {
        $this->load();
        if (empty($server)) {
            $server = $_SERVER;
        }

        return $this->_router->doRoute($req ?: $this->newRequest(), $server);
    }
}
