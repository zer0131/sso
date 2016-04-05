<?php

/**
 * @author: ryan<zer0131@vip.qq.com>
 * @desc: sso配置
 */

$common = array(
    'apps' => array(
        '1' => array(
            'app_key' => 'test',
            'callback_url' => '',
            'home_url' => ''
        )
    )
);

$online = array();

$dev = array();

return DEBUG ? array_merge($common, $dev) : array_merge($common, $online);
