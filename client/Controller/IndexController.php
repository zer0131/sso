<?php

/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc 默认控制器
 */
namespace Controller\Index;

use Controller\BaseController;

class IndexController extends BaseController {
    
    /**
     * 默认方法
     */
    public function indexAction(){
		$this->show();
    }
}
