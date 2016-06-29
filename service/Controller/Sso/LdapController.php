<?php

/**
 * @author ryan
 * @desc 模拟ldap
 */ 

namespace Controller\SSO;

use OneFox\Controller;
use OneFox\Request;

class LdapController extends Controller {

    //登录页[GET]
    public function indexAction() {
        $this->show();
    }

    //登陆提交[POST]
    public function loginAction() {
        if (Request::method() == 'post') {
            //
        } else {
            echo 'url error';
        }
    }
}
