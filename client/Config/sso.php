<?php
/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc sso相关配置
 */

$common = array(
    'check_code_url' => 'http://123.56.248.154:8085/sso/api/check_code',
    'check_auth_url' => 'http://123.56.248.154:8085/sso/api/check_ticket',
    'sso_login_url' => 'http://http://123.56.248.154:8085/sso/login/index',
    'sso_logout_url' => 'http://123.56.248.154:8085/sso/logout/index',
    'app_id' => '1',
    'app_key' => 'c4ca4238a0b923820dcc509a6f75849b',
    'sso_path' => '/',
    'sso_domain' => '',
);

$online = array();

$dev = array();

return DEBUG ? array_merge($common, $dev) : array_merge($common, $online);