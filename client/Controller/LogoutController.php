<?php
/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc 退出controller
 */

namespace Controller;

use SSO\Client;

use OneFox\ApiController as BaseController;

class LogoutController extends BaseController {

    public function indexAction() {
        $ssoClient = new Client();
        $ssoClient->logout();
    }
}