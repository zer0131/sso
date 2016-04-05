<?php

/**
 * @author ryan
 * @desc sso接口控制器
 */
namespace Controller\Sso;

use OneFox\ApiController as BaseController;
use OneFox\Request;

class ApiController extends BaseController {
    
    protected function _init() {
        if (Request::method() != 'post') {
            $this->json(self::CODE_FAIL, 'error');
        }
    }

    /**
     * 校验code[POST]
     */ 
    public function check_codeAction() {
        $code = $this->post('code');
        $appKey = $this->post('app_key');
        $appId = $this->post('app_id');
        if (!$code || !$appKey || !$appId) {
            $this->json(self::CODE_FAIL, 'params error');
        }
    }


    /**
     * 校验ticket[POST]
     */ 
    public function check_ticketAction() {
        //code
    }
}
