<?php

/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc 默认控制器
 */
namespace Controller;

use OneFox\Controller;

class IndexController extends Controller {
    
    /**
     * 默认方法
     */
    public function indexAction(){
        echo 'ok';
		//$this->show();
    }
}
