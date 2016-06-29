<?php

/**
 * @author ryan
 * @desc ticket操作类
 */ 

namespace SSO;

use OneFox\Crypt;

class Ticket {
    const PREFIX = 'sso_ticket:';

    /**
     * 生成子系统ticket
     * @param string $str
     * @param string $prefix
     * @return string
     */
    public function genSubTicket($str, $prefix=self::PREFIX){
        return Crypt::encode($prefix.$str);
    }

    /**
     * 生成服务端ticket
     * @param string $str
     * @param string $prefix
     * @return string
     */
    public function genSysTicket($str, $prefix=self::PREFIX){
        return Crypt::encode($prefix.$str);
    }

    /**
     * 依据ticket返回原始值
     * @param string $ticket
     * @param string $prefix
     * @return string
     */
    public function originData($ticket, $prefix=self::PREFIX){
        $decodeStr = Crypt::decode($ticket);
        return substr($decodeStr, strlen($prefix));
    }

    /**
     * 校验ticket
     * @param string $encodedStr
     */
    public function checkTicket($ticket){
        $sessionId = $this->originData($ticket);
        $sessObj = new Session();
        return $sessObj->isExists($sessionId);//使用session校验
    }
}
