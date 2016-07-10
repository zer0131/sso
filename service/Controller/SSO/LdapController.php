<?php

/**
 * @author ryan
 * @desc 模拟ldap
 */

namespace Controller\SSO;

use OneFox\Controller;

class LdapController extends Controller {

    //登录页[GET]
    public function indexAction() {
        $this->show();
    }
}
