<?php

/**
 * @author ryan
 * @desc 默认控制器
 */
namespace Controller\Index;

use OneFox\ApiController;

class IndexController extends ApiController {
    
    /**
     * 默认方法
     */
    public function indexAction(){
        $this->json(self::CODE_SUCCESS, '请访问/sso/login');
    }
}
