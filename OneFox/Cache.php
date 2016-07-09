<?php

/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc 缓存抽象类
 */

namespace OneFox;

abstract class Cache {
    protected static $instance;
    protected $options = array();

    public static function getInstance() {
        if (!self::$instance) {
            $type = Config::get('cache.type', 'file');
            $class = "\\OneFox\\Caches\\" . 'C' . ucwords(strtolower($type));
            self::$instance = new $class();
        }
        return self::$instance;
    }

    abstract public function get($name);//获取缓存

    abstract public function set($name, $value, $expire = null);//设置缓存

    abstract public function rm($name, $ttl = 0);//删除缓存

    abstract public function clear();//清除缓存
}
