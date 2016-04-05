<?php

/**
 * @author: ryan<zer0131@vip.qq.com>
 * @desc: 默认配置文件
 */

$common = array(
    'test' => 'ryan zhang'
);

$online = array();

$dev = array();

return DEBUG ? array_merge($common, $dev) : array_merge($common, $online);

