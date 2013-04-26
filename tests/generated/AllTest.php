<?php
/**
 *  Router dispatcher generated by crodas/Dispatcher
 *
 *  https://github.com/crodas/Dispatcher
 *
 *  This is a generated file, do not modify it.
 */
namespace AllTest;

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
        $uri    = $server['REQUEST_URI'];
        $uri    = ($p = strpos($uri, '?')) ? substr($uri, 0, $p) : $uri;
        $parts  = array_values(array_filter(explode("/", $uri)));
        $length = count($parts);

        if (empty($server['REQUEST_METHOD'])) {
            $server['REQUEST_METHOD'] = 'GET';
        }

        switch ($server["REQUEST_METHOD"]) {
        case 'POST':
            switch ($length) {
            case 1:
                // Routes for /prefix/ {{{
                if ($parts[0] === 'prefix') {
                    if (empty($file_ce8f643f)) {
                       $file_ce8f643f = 1;
                       require_once __DIR__ . "/../input/class.php";
                    }
                    if (empty($obj_filt_2d89b930)) {
                        $obj_filt_2d89b930 = new \SomeClass;
                    }
                
                    //run preRoute filters (if any)
                    $allow = true;
                if (empty($file_e0cf7353)) {
                   $file_e0cf7353 = 1;
                   require_once __DIR__ . "/../input/route_filters.php";
                }
                    if ($allow) {
                        $allow &= \CheckSession($req, array (
                ));
                    }
                
                    // do route
                    if ($allow) {
                        $return = $obj_filt_2d89b930->save($req);
                
                        // post postRoute (if any)
                
                        return $return;
                    }
                }
                // }}} end of /prefix/
                break;
            case 2:
                // Routes for /foobar/12345/ {{{
                if ($parts[0] === 'foobar' && $parts[1] === '12345') {
                    if (empty($file_b40be1df)) {
                       $file_b40be1df = 1;
                       require_once __DIR__ . "/../input/method.php";
                    }
                    if (empty($obj_filt_e02f213c)) {
                        $obj_filt_e02f213c = new \SomeMethodController;
                    }
                
                    //run preRoute filters (if any)
                    $allow = true;
                
                    // do route
                    if ($allow) {
                        $return = $obj_filt_e02f213c->modify($req);
                
                        // post postRoute (if any)
                
                        return $return;
                    }
                }
                // }}} end of /foobar/12345/
                break;
            case 3:
                // Routes for /foobar/12345//something {{{
                if ($parts[0] === 'foobar' && $parts[1] === '12345' && $parts[2] === 'something') {
                    if (empty($file_b40be1df)) {
                       $file_b40be1df = 1;
                       require_once __DIR__ . "/../input/method.php";
                    }
                    if (empty($obj_filt_e02f213c)) {
                        $obj_filt_e02f213c = new \SomeMethodController;
                    }
                
                    //run preRoute filters (if any)
                    $allow = true;
                
                    // do route
                    if ($allow) {
                        $return = $obj_filt_e02f213c->modify_something($req);
                
                        // post postRoute (if any)
                
                        return $return;
                    }
                }
                // }}} end of /foobar/12345//something
                break;
            }
            break;
        case 'GET':
            switch ($length) {
            case 2:
                // Routes for /foobar/12345/ {{{
                if ($parts[0] === 'foobar' && $parts[1] === '12345') {
                    if (empty($file_b40be1df)) {
                       $file_b40be1df = 1;
                       require_once __DIR__ . "/../input/method.php";
                    }
                    if (empty($obj_filt_e02f213c)) {
                        $obj_filt_e02f213c = new \SomeMethodController;
                    }
                
                    //run preRoute filters (if any)
                    $allow = true;
                
                    // do route
                    if ($allow) {
                        $return = $obj_filt_e02f213c->get($req);
                
                        // post postRoute (if any)
                
                        return $return;
                    }
                }
                // }}} end of /foobar/12345/
                break;
            }
            break;
        case 'DELETE':
            switch ($length) {
            case 2:
                // Routes for /foobar/12345/ {{{
                if ($parts[0] === 'foobar' && $parts[1] === '12345') {
                    if (empty($file_b40be1df)) {
                       $file_b40be1df = 1;
                       require_once __DIR__ . "/../input/method.php";
                    }
                    if (empty($obj_filt_e02f213c)) {
                        $obj_filt_e02f213c = new \SomeMethodController;
                    }
                
                    //run preRoute filters (if any)
                    $allow = true;
                
                    // do route
                    if ($allow) {
                        $return = $obj_filt_e02f213c->modify($req);
                
                        // post postRoute (if any)
                
                        return $return;
                    }
                }
                // }}} end of /foobar/12345/
                break;
            case 3:
                // Routes for /foobar/12345//something {{{
                if ($parts[0] === 'foobar' && $parts[1] === '12345' && $parts[2] === 'something') {
                    if (empty($file_b40be1df)) {
                       $file_b40be1df = 1;
                       require_once __DIR__ . "/../input/method.php";
                    }
                    if (empty($obj_filt_e02f213c)) {
                        $obj_filt_e02f213c = new \SomeMethodController;
                    }
                
                    //run preRoute filters (if any)
                    $allow = true;
                
                    // do route
                    if ($allow) {
                        $return = $obj_filt_e02f213c->modify_something($req);
                
                        // post postRoute (if any)
                
                        return $return;
                    }
                }
                // }}} end of /foobar/12345//something
                break;
            }
            break;
        }
        
        switch ($length) {
        case 2:
            // Routes for /function/reverse {{{
            if ($parts[0] === 'function' && $parts[1] === 'reverse') {
                if (empty($file_2053a8ae)) {
                   $file_2053a8ae = 1;
                   require_once __DIR__ . "/../input/functions.php";
                }
            
                //run preRoute filters (if any)
                $allow = true;
            
                // do route
                if ($allow) {
                    $return = \some_function($req);
            
                    // post postRoute (if any)
            
                    return $return;
                }
            }
            // }}} end of /function/reverse
            
            // Routes for /prefix//some {{{
            if ($parts[0] === 'prefix' && $parts[1] === 'some') {
                if (empty($file_ce8f643f)) {
                   $file_ce8f643f = 1;
                   require_once __DIR__ . "/../input/class.php";
                }
                if (empty($obj_filt_2d89b930)) {
                    $obj_filt_2d89b930 = new \SomeClass;
                }
            
                //run preRoute filters (if any)
                $allow = true;
            
                // do route
                if ($allow) {
                    $return = $obj_filt_2d89b930->index($req);
            
                    // post postRoute (if any)
            
                    return $return;
                }
            }
            // }}} end of /prefix//some
            
            if (empty($file_e55749ee)) {
               $file_e55749ee = 1;
               require_once __DIR__ . "/../input/filter.php";
            }
            if (empty($obj_filt_91adc016)) {
                $obj_filt_91adc016 = new \SomeSillyClass;
            }
            // Routes for /ifempty/{something:algo-alias} {{{
            if ($parts[0] === 'ifempty' && (!empty($filter_1ded59a9_1) || ($filter_1ded59a9_1=$this->doCachedFilter(array($obj_filt_91adc016, 'filter_set'), $req, 'algo-alias', $parts[1], 1)))) {
                $req->setIfEmpty('algo-alias', $parts[1]);
                if (empty($file_2053a8ae)) {
                   $file_2053a8ae = 1;
                   require_once __DIR__ . "/../input/functions.php";
                }
            
                //run preRoute filters (if any)
                $allow = true;
            
                // do route
                if ($allow) {
                    $return = \some_function($req);
            
                    // post postRoute (if any)
            
                    return $return;
                }
            }
            // }}} end of /ifempty/{something:algo-alias}
            
            if (empty($file_e55749ee)) {
               $file_e55749ee = 1;
               require_once __DIR__ . "/../input/filter.php";
            }
            if (empty($obj_filt_91adc016)) {
                $obj_filt_91adc016 = new \SomeSillyClass;
            }
            // Routes for /function/{reverse} {{{
            if ($parts[0] === 'function' && (!empty($filter_75470a30_1) || ($filter_75470a30_1=$obj_filt_91adc016->filter_reverse($req, 'reverse', $parts[1])))) {
                $req->setIfEmpty('reverse', $parts[1]);
                if (empty($file_2053a8ae)) {
                   $file_2053a8ae = 1;
                   require_once __DIR__ . "/../input/functions.php";
                }
            
                //run preRoute filters (if any)
                $allow = true;
            
                // do route
                if ($allow) {
                    $return = \some_function($req);
            
                    // post postRoute (if any)
            
                    return $return;
                }
            }
            // }}} end of /function/{reverse}
            break;
        case 1:
            // Routes for /deadly-simple {{{
            if ($parts[0] === 'deadly-simple') {
                if (empty($file_2053a8ae)) {
                   $file_2053a8ae = 1;
                   require_once __DIR__ . "/../input/functions.php";
                }
            
                //run preRoute filters (if any)
                $allow = true;
            
                // do route
                if ($allow) {
                    $return = \simple($req);
            
                    // post postRoute (if any)
            
                    return $return;
                }
            }
            // }}} end of /deadly-simple
            
            // Routes for /zzzsfasd_prefix_{id} {{{
            if (preg_match('/zzzsfasd_prefix_(.+)/', $parts[0], $matches_0) > 0) {
                $req->setIfEmpty('id', $matches_0[1]);
                if (empty($file_2053a8ae)) {
                   $file_2053a8ae = 1;
                   require_once __DIR__ . "/../input/functions.php";
                }
            
                //run preRoute filters (if any)
                $allow = true;
            
                // do route
                if ($allow) {
                    $return = \soo($req);
            
                    // post postRoute (if any)
            
                    return $return;
                }
            }
            // }}} end of /zzzsfasd_prefix_{id}
            
            if (empty($file_2053a8ae)) {
               $file_2053a8ae = 1;
               require_once __DIR__ . "/../input/functions.php";
            }
            // Routes for /{__id__} {{{
            if ((!empty($filter_99149840_0) || ($filter_99149840_0=\__filter__($req, '__id__', $parts[0])))) {
                $req->setIfEmpty('__id__', $parts[0]);
                if (empty($file_2053a8ae)) {
                   $file_2053a8ae = 1;
                   require_once __DIR__ . "/../input/functions.php";
                }
            
                //run preRoute filters (if any)
                $allow = true;
            
                // do route
                if ($allow) {
                    $return = \empty_level_1($req);
            
                    // post postRoute (if any)
            
                    return $return;
                }
            }
            // }}} end of /{__id__}
            break;
        case 0:
            // Routes for / {{{
                if (empty($file_2053a8ae)) {
                   $file_2053a8ae = 1;
                   require_once __DIR__ . "/../input/functions.php";
                }
            
                //run preRoute filters (if any)
                $allow = true;
            
                // do route
                if ($allow) {
                    $return = \empty_level_2($req);
            
                    // post postRoute (if any)
            
                    return $return;
                }
            // }}} end of /
            break;
        }

        throw new NotFoundException;
    }
}
