<?php

/**
 * @author ryan
 * @desc session操作类
 */ 

namespace Lib\SSO;

use OneFox\Cache;

class Session {
    private $_redis;
    private $_ticket;

    const SESSION_PREFIX = 'sso_sess:'; 
    const TTL = 604800;//默认有效期1周

    public function __construct() {
        $this->_redis = Cache::getInstance();
    }

    /**
     * 生成session
     * @param array $data 关联数组
     * @return string
     */
    public function create($data, $ttl=self::TTL){
        if (!is_array($data)) {
            return '';
        }
        $sessionId = self::SESSION_PREFIX.md5(uniqid(microtime(true), true));
        if ($this->_redis->set($sessionId, json_encode($data))) {
            if ($this->_redis->expireAt($sessionId, time()+$ttl)) {
                return $sessionId;
            } else {
                $this->_redis->rm($sessionId);
            }
        }
        return '';
    }

    /**
     * 延长session有效期
     * @param string $sessionId
     * @param int $ttl
     * @return boolean
     */
    public function extendedTime($sessionId='', $ttl=self::TTL){
        if (empty($sessionId)) {
            $sessionId = $this->getSessionid();
        }
        if ($this->_redis->exists($sessionId)) {
            if ($this->_redis->expireAt($sessionId, time()+$ttl)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 删除单个session
     * @param string $sessionId
     * @return boolean
     */
    public function delete($sessionId=''){
        if (empty($sessionId)) {
            $sessionId = $this->getSessionid();
        }
        if ($this->_redis->exists($sessionId)) {
            $res = $this->_redis->rm($sessionId);
            if (intval($res) === 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取session数据
     * @param string $sessionId
     * @param string $key 
     * @return mixed
     */
    public function getSession($sessionId='', $key=''){
        if (empty($sessionId)) {
            $sessionId = $this->getSessionid();
        }
        if ($this->_redis->exists($sessionId)) {
            $res = $this->_redis->get($sessionId);
            $r = json_decode($res, true);
            if (empty($key)) {
                return $r;
            }
            return isset($r[$key]) ? $r[$key] : false;
        }
        return false;
    }

    /**
     * 从cookie取出session
     * @return string
     */
    public function getSessionid(){
        $cookieName = md5(\OneFox\Config::get('sso.cookie_name'));
        $cookie = \OneFox\Request::cookie($cookieName);
        $ticketObj = new Ticket();
        return $ticketObj->originData($cookie);
    }

    /**
     * 判断session是否存在
     * @param string $sessionId
     * @return type
     */
    public function isExists($sessionId){
        return $this->_redis->exists($sessionId);
    }
}
