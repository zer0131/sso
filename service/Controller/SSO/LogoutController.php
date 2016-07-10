<?php

/**
 * @author ryan
 * @desc sso退出控制器
 */

namespace Controller\SSO;

use OneFox\ApiController;
use OneFox\Config;
use OneFox\Request;
use OneFox\Response;
use SSO\Session;
use SSO\Ticket;

class LogoutController extends ApiController {

    public function indexAction() {
        $appId = $this->get('app_id', 0, 'int');
        if (!$appId) {
            echo 'params error';
            return;
        }
        $jumpto = $this->get('jumpto');

        $cookieName = md5(Config::get('sso.cookie_name'));
        $cookie = Request::cookie($cookieName);

        $ticketObj = new Ticket();
        $sessObj = new Session();
        $sessionId = $ticketObj->originData($cookie);

        //清除session
        $sessObj->delete($sessionId);

        //清除服务端cookie
        setcookie($cookieName, '', time() - 31500000, Config::get('sso.cookie_path'), '');
        $paramArr = array(
            'jumpto' => $jumpto ? urlencode($jumpto) : 'index',
            'app_id' => $appId
        );
        $param = http_build_query($paramArr);

        $loginUrl = Config::get('sso.login_url');
        $loginUrl .= '?' . $param;
        Response::redirect($loginUrl);
    }
}
