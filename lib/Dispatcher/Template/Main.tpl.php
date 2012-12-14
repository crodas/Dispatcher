<?php
/**
 *  Router dispatcher generated by crodas/Dispatcher
 *
 *  https://github.com/crodas/Dispatcher
 *
 *  This is a generated file, do not modify it.
 */
#* if ($config->getNamespace()) 
#* $namespace = $config->getNamespace()
namespace __namespace__;
#* end

class NotFoundException extends \Exception 
{
}

interface FilterCache
{
    public function has($key);
    public function set($key, $value, $ttl);
    public function get($key);
}

class Request
{
    protected $var = array();
    protected $changes = array();
    protected $watch   = false;

    public function watchChanges()
    {
        $this->watch   = true;
        $this->changes = array();
        return true;
    }

    public function getChanges()
    {
        $this->watch = false;
        return $this->changes;
    }

    public function setIfEmpty($name, $value)
    {
        if (empty($this->var[$name])) {
            $this->var[$name] = $value;
            if ($this->watch) {
                $this->changes[] = $name;
            }
        }
        return $this;
    }

    public function set($name, $value)
    {
        $this->var[$name] = $value;
        if ($this->watch) {
            $this->changes[] = $name;
        }
        return $this;
    }

    public function get($name)
    {
        if (array_key_exists($name, $this->var)) {
            return $this->var[$name];
        }
        return NULL;
    }
}

class Route
{
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

    public function fromRequest(Request $req = NULL)
    {
        if (empty($req)) {
            $req = new Request;
        }
        return $this->doRoute($req, $_SERVER);
    }

    public function doRoute(Request $req, $server)
    {
        $uri    =  ($p = strpos($server['REQUEST_URI'], '?')) ? substr($server['REQUEST_URI'], $p) : $server['REQUEST_URI'];
        $parts  = array_values(array_filter(explode("/", $server['REQUEST_URI'])));
        $length = count($parts);

        if (empty($server['REQUEST_METHOD'])) {
            $server['REQUEST_METHOD'] = 'GET';
        }

        #* render($groups)

        throw new NotFoundException;
    }
}
