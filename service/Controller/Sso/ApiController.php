<?php

/**
 * @author ryan
 * @desc sso接口控制器
 */
namespace Controller\Sso;

use OneFox\ApiController as BaseController;
use OneFox\Request;
use Lib\SSO\Code;
use Lib\SSO\Session;
use Lib\SSO\Ticket;

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
        $appId = $this->post('app_id', 0, 'int');
        if (!$code || !$appKey || !$appId) {
            $this->json(self::CODE_FAIL, 'params error');
        }
        $codeObj = new Code();
        $res = $codeObj->checkCode($code);//返回session id
        if (empty($res)) {
            $this->json(self::CODE_FAIL, 'check code error');
        }

        //获取session
        $sessObj = new Session(); 
        $sessionData = $sessObj->getSession($res);

        //生成子系统ticket
        $ticketObj = new Ticket();
        $ticket = $ticketObj->genSubTicket($res);

        //返回数据
        $data = array('ticket'=>$ticket, 'username'=>$sessionData['username']);
        $this->json(self::CODE_SUCCESS, 'ok', $data);
    }


    /**
     * 校验ticket[POST]
     */ 
    public function check_ticketAction() {
        $ticket = $this->post('ticket');
        $appId = $this->post('app_id', 0, 'int');
        if (!$appId || !$ticket) {
            $this->json(self::CODE_FAIL, 'params error');
        }
        $ticketObj = new Ticket();
        $sessionId = $ticketObj->originData($ticket);
        $sessObj = new Session();

        //session超时或不存在
        if (!$sessObj->isExists($sessionId)) {
            $this->json(self::CODE_FAIL, 'check ticket error');
        }

        //重置session过期时间
        $sessObj>extendedTime($sessionId);

        $this->json(self::CODE_SUCCESS, 'ok');
    }
}
