<?php

/**
 * @author ryan
 * @desc 默认控制器
 */
namespace Controller\Index;

use OneFox\Controller;

class IndexController extends Controller {

    /**
     * 默认方法
     */
    public function indexAction() {
        $url = 'http://www.appryan.com/php/2016/04/02/sso.html';
        header('Content-type:text/html;charset=utf-8');
        echo '请阅读详细手册: '.$url;
    }
}
