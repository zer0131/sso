<?php

/** 
 * @author: ryan<zer0131@vip.qq.com>
 * @desc: Api接口控制器
 */

namespace OneFox;

abstract class ApiController {
    
    const CODE_SUCCESS = 0;
    const CODE_FAIL = 1;

    public function __construct() {
        //此方法可初始化控制器
        if (method_exists($this, '_init')){
            $this->_init();
        }
    }

    /**
     * json输出
     * @params status int 状态码
     * @params msg string 提示信息
     * @params data array 返回数据
     * @params ext array 扩展返回数据，一位数组
     */
    protected function json($status, $msg, $data=null, $ext=null) {
        $res = array(
            'status' => $status,
            'msg' => $msg,
            'data' => $data
        );
        if ($ext && is_array($ext)) {
            foreach ($ext as $k => $v) {
                $res[$k] = $v;
            }
        }
        Response::json($res);
    }

    /**
     * 获取GET请求参数
     */
    protected function get($key, $default='', $type='str') {
        return Request::get($key, $default, $type);
    }

    /**
     * 获取POST请求参数
     */
    protected function post($key, $default='', $type='str') {
        return Request::post($key, $default, $type);
    }

    /**
     * 获取PUT请求参数
     */
    protected function put($key, $default='', $type='str') {
        if (Request::method() !== 'put') {
            return $default;
        }
        $json = Request::stream();
        $data = json_decode($json, true);
        if (!$data) {
            return $default;
        }
        $data = Request::filterArray($data);
        return Request::filter($key, $data, $default, $type);
    }

    /**
     * 获取DELETE请求参数
     */
    protected function delete($key, $default='', $type='str') {
        if (Request::method() !== 'delete') {
            return $default;
        }
        $json = Request::stream();
        $data = json_decode($json, true);
        if (!$data) {
            return $default;
        }
        $data = Request::filterArray($data);
        return Request::filter($key, $data, $default, $type);
    }
    
    /**
     * 获取PATCH请求参数
     */
    protected function patch($key, $default='', $type='str') {
        if (Request::method() !== 'patch') {
            return $default;
        }
        $json = Request::stream();
        $data = json_decode($json, true);
        if (!$data) {
            return $default;
        }
        $data = Request::filterArray($data);
        return Request::filter($key, $data, $default, $type);
    }
}

