<?php

/** 
 * @author ryan<zer0131@vip.qq.com>
 * @desc 统一入口
 */

if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

//--------定义目录分隔符--------//
define('DS', DIRECTORY_SEPARATOR);

//--------定义应用目录(必须)--------//
define('APP_PATH', dirname(dirname(__FILE__)));

//--------定义框架目录(必须)--------//
define('ONEFOX_PATH',  dirname(APP_PATH).DS.'OneFox');

//--------开启模块模式(Controller目录下含有子目录, 默认开启)--------//
define('MODULE_MODE', false);

//--------是否开启调试模式(默认关闭)--------//
define('DEBUG', true);

//--------日志目录--------//
define('LOG_PATH', APP_PATH.DS.'Log');

//--------配置目录--------//
define('CONF_PATH', APP_PATH.DS.'Config');

//--------模板目录--------//
define('TPL_PATH', APP_PATH.DS.'Tpl');

require ONEFOX_PATH.DS.'Onefox.php';
