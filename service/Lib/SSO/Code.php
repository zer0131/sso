<?php

/**
 * @author ryan
 * @desc code操作类
 */ 

namespace Lib\SSO;

use OneFox\Cache;

class Code {
    const PREFIX = 'sso_code:';
    const TOKEN_EXPIRE = 30;//缓存30秒

    private $_cache;

    public function __construct() {
        $this->_cache = Cache::getInstance();
    }

    /**
     * 生成code
     * @param string $data
     * @param int $cacheTime
     * @return string
     */
    public function generateCode($data, $cacheTime=self::TOKEN_EXPIRE){
        $cacheKey = md5(uniqid(microtime(true), true));
        if ($this->_cache->set(self::PREFIX.$cacheKey, $data, $cacheTime)) {
            return $cacheKey;
        }
        return '';
    }

    /**
     * 校验code(校验成功，返回缓存的数据)
     * @param string $code
     * @return string
     */
    public function checkCode($code){
        $data = $this->_cache->get(self::PREFIX.$code);
        if (!$data) {
            return '';
        } else {
            $this->_cache->rm(self::PREFIX.$code);
            return $data;
        }
    }
}
