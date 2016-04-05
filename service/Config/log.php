<?php

/**
 * @author: ryan<zer0131@vip.qq.com>
 * @desc: 日志配置
 */

$common = array(
    'default' => array()
);

$online = array();

$dev = array();

return DEBUG ? array_merge($common, $dev) : array_merge($common, $online);
