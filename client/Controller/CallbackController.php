<?php

/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc 回调controller
 */

namespace Controller;

use OneFox\ApiController as BaseController;
use OneFox\Request;
use SSO\Client;
use OneFox\Response;

class CallbackController extends BaseController {

    public function indexAction() {
        $code = Request::get('code');
        if (!$code) {
            echo 'code not found';
            exit;
        }
        $jumpto = urldecode(Request::get('jumpto'));
        $ssoClient = new Client();
        $login = $ssoClient->checkLogin();
        if (!$login) {
            $res = $ssoClient->validCode($code);
            if ($res === false) {
                echo 'Code Error';
                exit;
            }
            $ssoClient->setLogin($res['username'], $res['ticket']);
        } else {
            $ssoClient->refreshTicket();
        }
        if (!$jumpto || $jumpto == 'index') {
            $jumpto = '/';
        }
        Response::redirect($jumpto, 0);
    }
}