<?php

/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc 默认控制器
 */
namespace Controller;

use OneFox\ApiController as BaseController;
use SSO\Client;
use OneFox\Request;

class IndexController extends BaseController {

    protected function _init() {
        $ssoClient = new Client();
        $login = $ssoClient->checkLogin();
        if (!$login) {
            //处理ajax请求
            if (Request::isAjax()) {
                $this->json(self::CODE_FAIL, 'no login');
            }
            $currentUrl = $_SERVER['REQUEST_URI'];//获取当前页面的地址
            $redirect = $ssoClient->getSsoCenterJumpUrl($currentUrl);
            header("location: " . $redirect);
            exit;
        }
        $ssoClient->refreshTicket();
    }

    /**
     * 模拟系统首页
     */
    public function indexAction() {
        $url = 'http://www.appryan.com/php/2016/04/02/sso.html';
        header('Content-type:text/html;charset=utf-8');
        echo '请阅读详细手册: ' . $url;
    }
}
