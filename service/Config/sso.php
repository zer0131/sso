<?php

/**
 * @author: ryan<zer0131@vip.qq.com>
 * @desc: sso配置
 */

$common = array(
    'cookie_name' => 'sso_ticket',
    'login_url' => '/sso/ldap',
    'apps' => array(
        '1' => array(
            'app_key' => 'c4ca4238a0b923820dcc509a6f75849b',
            'callback_url' => 'http://123.56.248.154:8085/callback',
            'home_url' => 'http://123.56.248.154:8085'
        )
    )
);

$online = array();

$dev = array();

return DEBUG ? array_merge($common, $dev) : array_merge($common, $online);
