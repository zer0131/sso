<?php
/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc sso客户端封装类
 */

namespace SSO;

use OneFox\Config;
use OneFox\Curl;

class Client {

    const TICKET_COOKIE_NAME = 'SUB_TICKET';
    const USER_COOKIE_NAME = 'UID';
    const COOKIE_EXPIRED = 14400;
    const exception_init_error = -1;

    protected $_appid = null;
    protected $_appkey = null;
    protected $_cookiePath = null;
    protected $_cookiePre = 'SSO_AS_';
    protected $_cookieDomain = "";
    protected $_code = '';
    protected $_ssoCheckCodeUrl = '';
    protected $_ssoAuthUrl = '';
    protected $_ssoLoginUrl = '';
    protected $_ssoLogoutUrl = '';

    public function __construct() {
        $ssoConfig = Config::get('sso');
        $this->_appid = $ssoConfig['app_id'];
        $this->_appkey = $ssoConfig['app_key'];
        $this->_cookiePath = $ssoConfig['sso_path'];
        $this->_cookieDomain = $ssoConfig['sso_domain'];
        $this->_ssoAuthUrl = $ssoConfig['check_auth_url'];
        $this->_ssoCheckCodeUrl = $ssoConfig['check_code_url'];
        $this->_ssoLoginUrl = $ssoConfig['sso_login_url'];
        $this->_ssoLogoutUrl = $ssoConfig['sso_logout_url'];
    }

    public function checkLogin() {
        if (!$ticket = $this->getTicket()) {
            return false;
        } else {
            $params = array(
                'ticket' => $ticket,
                'app_id' => $this->_appid
            );
            $curl = new Curl();
            $res = $curl->post($this->_ssoAuthUrl, $params);
            $r = json_decode($res, true);
            if (intval($r['errno']) === 0) {
                return true;
            }
            return false;
        }
    }

    public function getSsoCenterJumpUrl($currentUrl) {
        return $this->_ssoLoginUrl . '?app_id=' . $this->_appid . '&jumpto=' . urlencode($currentUrl) . '&version=' . urlencode('1.0.0');
    }

    public function setLogin($username, $ticket) {
        setcookie($this->_cookiePre . self::TICKET_COOKIE_NAME, $ticket, time() + self::COOKIE_EXPIRED, $this->_cookiePath, $this->_cookieDomain, false, true);
        setcookie($this->_cookiePre . self::USER_COOKIE_NAME, $username, time() + self::COOKIE_EXPIRED, $this->_cookiePath, $this->_cookieDomain, false, true);
    }

    public function refreshTicket() {
        $ticket = $this->getTicket();
        setcookie($this->_cookiePre . self::TICKET_COOKIE_NAME, $ticket, time() + self::COOKIE_EXPIRED, $this->_cookiePath, $this->_cookieDomain, false, true);
        $username = $this->getUsername();
        setcookie($this->_cookiePre . self::USER_COOKIE_NAME, $username, time() + self::COOKIE_EXPIRED, $this->_cookiePath, $this->_cookieDomain, false, true);
    }

    public function getTicket() {
        $ticket = $_COOKIE[$this->_cookiePre . self::TICKET_COOKIE_NAME];
        return $ticket;
    }

    public function getUsername() {
        $username = $_COOKIE[$this->_cookiePre . self::USER_COOKIE_NAME];
        return $username;
    }

    public function validCode($code) {
        $params = array(
            'code' => $code,
            'app_key' => $this->_appkey,
            'app_id' => $this->_appid
        );
        $curl = new Curl();
        $res = $curl->post($this->_ssoCheckCodeUrl, $params);
        $r = json_decode($res, true);
        if (intval($r['errno']) === 0) {
            return $r['data'];//返回ticket和username
        }
        return false;
    }

    public function logout() {
        //清理本地cookie
        setcookie($this->_cookiePre . self::TICKET_COOKIE_NAME, '', time() - 31500000, $this->_cookiePath, $this->_cookieDomain);
        setcookie($this->_cookiePre . self::USER_COOKIE_NAME, '', time() - 31500000, $this->_cookiePath, $this->_cookieDomain);
        //重定向至sso logout
        header("location: " . $this->_ssoLogoutUrl . '?app_id=' . $this->_appid);
        exit;
    }
}