<?php

/**
 * @author ryan
 * @desc sso登陆控制器 
 */
namespace Controller\Sso;

use OneFox\ApiController;

class LoginController extends ApiController {
    
    public function indexAction() {
        $this->json(self::CODE_SUCCESS, 'sso login');
    }
}
