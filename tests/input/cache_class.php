<?php

class TestCacheClass implements Dispatcher\FilterCache
{
    protected $cache = array();

    function has($key) {
        return array_key_exists($key, $this->cache);
    }

    function set($key, $value, $ttl) {
        $this->cache[$key] = $value;
    }

    function get($key) {
        if (!$this->has($key)) {
            return NULL;
        }
        return $this->cache[$key];
    }

    public static function getInstance() {
        static $self;
        if (empty($self)) {
            $self = new self;
        }
        return $self;
    }
}
