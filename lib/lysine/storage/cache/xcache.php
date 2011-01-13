<?php
namespace Lysine\Storage\Cache;

use Lysine\Error;
use Lysine\Storage\Cache;

class Xcache extends Cache {
    protected $life_time = 300;

    public function __construct(array $config) {
        if (!extension_loaded('xcache'))
            throw Error::require_extension('xcache');

        parent::__construct($config);
    }

    public function set($key, $val, $life_time = null) {
        if ($life_time === null) $life_time = $this->life_time;
        if (DEBUG) \Lysine\logger('cache')->debug('Xcache set key '. (is_array($key) ? implode(',', $key) : $key) .' with life_time '. $life_time);

        $key = $this->makeKey($key);
        return xcache_set($key, $val, $life_time);
    }

    public function mset(array $data, $life_time = null) {
        foreach ($data as $key => $val) $this->set($key, $val, $life_time);
    }

    public function get($key) {
        if (DEBUG) \Lysine\logger('cache')->debug('Xcache get key '. (is_array($key) ? implode(',', $key) : $key));

        $key = $this->makeKey($key);
        return xcache_isset($key) ? xcache_get($key) : false;
    }

    public function mget(array $keys) {
        $result = array();
        foreach ($keys as $key) $result[$key] = $this->get($key);
        return $result;
    }

    public function delete($key) {
        if (DEBUG) \Lysine\logger('cache')->debug('Xcache delete key '. (is_array($key) ? implode(',', $key) : $key));

        $key = $this->makeKey($key);
        return xcache_unset($key);
    }

    public function mdelete(array $keys) {
        foreach ($keys as $key) $this->delete($key);
    }
}
