<?php

namespace Dispatcher;

interface FilterCache
{
    public function has($key);
    public function set($key, $value, $ttl);
    public function get($key);
}
