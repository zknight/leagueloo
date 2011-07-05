<?php

class Cache
{
    static public $enabled = false;

    static public function Write($key, $var)
    {
        if (Cache::$enabled == true)
        {
            return apc_store($key, $var);
        }
        return false;
    }

    static public function Read($key)
    {
        if (Cache::$enabled == true)
        {
            return apc_fetch($key);
        }
        return false;
    }

    static public function Reset()
    {
        if (Cache::$enabled == true)
        {
            apc_clear_cache('user');
        }
    }
}

Cache::$enabled = function_exists('apc_fetch');
