<?php

/** 
 * @author ryan<zer0131@vip.qq.com>
 * @desc redis缓存类
 */

namespace OneFox\Caches;

use OneFox\Cache;
use OneFox\Config;

class CRedis extends Cache {

	private $_redis;

	public function __construct() {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('redis扩展未加载');
        }
        $this->options = Config::get('cache.redis');
        if (!$this->options) {
            $this->options = array(
                'expire' => 0,
                'prefix' => 'onefox_',
                'server' => array(
                    'host' => '127.0.0.1',
                    'port' => 6379
                )
            );
        }
        $this->_connect();
	}

	private function _connect() {
        $this->_redis = new \Redis();
        $this->_redis->connect($this->options['server']['host'], $this->options['server']['port']);
	}

	public function get($name) {
        if (!$this->_redis) {
            $this->_connect();
        }
        return $this->_redis->get($this->options['prefix'].$name);
	}

	public function set($name, $value, $expire=null) {
        if ($this->_redis) {
            $this->_connect();
        }
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        if (intval($expire) === 0) {
            return $this->_redis->set($this->options['prefix'].$name, $value);
        } else {
            return $this->_redis->setEx($this->options['prefix'].$name, intval($expire), $value);
        }
	}

	public function rm($name, $ttl=0) {
        if (!$this->_redis) {
            $this->_connect();
        }
        return $this->_redis->delete($this->options['prefix'].$name);
	}

	public function clear() {
        if (!$this->_redis) {
            $this->_connect();
        }
        return $this->_redis->flushAll();
	}

	public function __call($funcName, $arguments) {
        if (!$this->_redis) {
            $this->_connect();
        }
        $res = call_user_func_array(array($this->_redis, $funcName), $arguments);
        return $res;
	}

	public function __destruct() {
        $this->_redis->close();
        if ($this->_redis) {
            $this->_redis = null;
        }
	}
}
