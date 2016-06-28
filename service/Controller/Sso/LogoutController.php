<?php

/**
 * @author ryan
 * @desc sso退出控制器
 */ 

namespace Controller\Sso;

use OneFox\ApiController;
use OneFox\Config;
use OneFox\Request;
use Lib\SSO\Session;
use Lib\SSO\Ticket;
use OneFox\Response;

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
        setcookie($cookieName, '', time()-31500000, Config::get('sso.cookie_path'), '');
        $paramArr = array(
            'jumpto' => $jumpto ? urlencode($jumpto) : 'index',
            'app_id' => $appId
        );
        $param = http_build_query($paramArr);

        $loginUrl = Config::get('sso.login_url');
        $loginUrl .= '?'.$param;
        Response::redirect($loginUrl);
    }
}
