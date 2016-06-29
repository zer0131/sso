<?php

/** 
 * @author ryan<zer0131@vip.qq.com>
 * @desc 核心入口文件
 */

namespace OneFox;

final class Onefox {
    
    private static $_ext = '.php';
    private static $_startTime = 0;
    private static $_memoryStart = 0;
    private static $_error;

    public static function start(){
        if (!defined('APP_PATH')) {
            die('APP_PATH is not difined.');
        }
        if (!defined('ONEFOX_PATH')) {
            die('ONEFOX_PATH is not defined.');
        }
        
        //--------设置时区--------//
        date_default_timezone_set("PRC");
        
        //--------定义常量--------//
        define('ONEFOX_VERSION', '1.0.4');
        define('REQUEST_ID', uniqid());
        define('IS_CLI',PHP_SAPI=='cli' ? true:false);
        !defined('DS') && define('DS', DIRECTORY_SEPARATOR);//目录分隔符
        !defined('MODULE_MODE') && define('MODULE_MODE', true);//默认开启模块模式(Controller目录下含有子目录)
        !defined('DEBUG') && define('DEBUG', false);//调试模式
        !defined('LOG_PATH') && define('LOG_PATH', APP_PATH.DS.'Log');//日志目录
        !defined('CONF_PATH') && define('CONF_PATH', APP_PATH.DS.'Config');//配置目录
        !defined('TPL_PATH') && define('TPL_PATH', APP_PATH.DS.'Tpl');//模板目录
        !defined('LIB_PATH') && define('LIB_PATH', APP_PATH.DS.'Lib');//扩展类库目录
        !defined('DEFAULT_MODULE') && define('DEFAULT_MODULE', 'Index');//默认执行模块
        !defined('DEFAULT_CONTROLLER') && define('DEFAULT_CONTROLLER', 'Index');//默认执行控制器
        !defined('DEFAULT_ACTION') && define('DEFAULT_ACTION', 'index');//默认执行方法
        !defined('XSS_MODE') && define('XSS_MODE', true);//开启XSS过滤
        !defined('ADDSLASHES_MODE') && define('ADDSLASHES_MODE', false);//不使用addslashes
        if(version_compare(PHP_VERSION,'5.4.0','<')){
            ini_set('magic_quotes_runtime',0);
            define('MAGIC_QUOTES_GPC',get_magic_quotes_gpc()?true:false);
        }
        else{
            define('MAGIC_QUOTES_GPC',false);
        }
        
        //--------设置错误级别, 记录程序开始时间及内存--------//
        if (DEBUG) {
            ini_set('display_errors', 'On');
            error_reporting(E_ALL ^ E_NOTICE);
            self::$_startTime = microtime(true);
            self::$_memoryStart = memory_get_usage(true);
        }
        
        //--------自动注册类--------//
        spl_autoload_register(array('OneFox\Onefox', 'autoload'));
        
        //--------运行结束执行--------//
        register_shutdown_function(array('OneFox\Onefox', 'end'));
        
        //--------自定义错误处理--------//
        set_error_handler(array('OneFox\Onefox', 'errorHandler'));
        
        //--------处理未捕捉的异常--------//
        set_exception_handler(array('OneFox\Onefox', 'exceptionHandler'));
       
        //--------处理请求数据--------//
        Request::deal();
        
        //--------简单路由--------//
        Dispatcher::dipatcher();
        
        //--------执行--------//
        self::_exec();
        
        return;
    }
    
    public static function autoload($className){
        $file = self::_parseClassPath($className);
        if (file_exists($file)) {
            require_once $file;
            return class_exists($className);
        }
        return false;
    }

    //解析路径
    private static function _parseClassPath($className) {
        $class = $className;
        $path = strtr($class, '\\', DS);
        $file = null;
        //加载框架文件
        if (0 === strpos($class, 'OneFox\\')) {
            $file = ONEFOX_PATH.substr($path, strlen('OneFox')).self::$_ext;
            return $file;
        }
        //加载应用目录下文件
        $file = APP_PATH.DS.$path.self::$_ext;
        if (file_exists($file)) {
            return $file;
        }
        //加载扩展库文件
        $file = LIB_PATH.DS.$path.self::$_ext;
        if (file_exists($file)) {
            return $file;
        }
        return $file;
    }
    
    private static function _exec(){
        define('CURRENT_CONTROLLER', Dispatcher::getControllerName());
        define('CURRENT_ACTION', Dispatcher::getActionName());
        $controllerName = CURRENT_CONTROLLER.'Controller';
        $currModule = Dispatcher::getModuleName();
        $moduleName = '';
        $realModuleName = '';
        $className = '';
        if (!empty($currModule)) {
            //module名称兼容大小写
            $moduleName = array(
                ucfirst(strtolower($currModule)),//首字母大写
                strtoupper($currModule),//大写
                strtolower($currModule),//小写
            );
        }
        if (is_array($moduleName)) {
            foreach ($moduleName as $v) {
                $className = sprintf('Controller\\%s\\%s', $v, $controllerName);
                $realModuleName = $v;
                if (class_exists($className)) {
                    break;
                }
            }
        } else {
            $className = sprintf("Controller\\%s", $controllerName);
        }
        define('CURRENT_MODULE', $realModuleName);
        //-----请求日志------//
        $params = array();
        $log = array(
            'request' => $_SERVER['REQUEST_URI'],
            'request_id' => REQUEST_ID,
            'class' => array('module'=>CURRENT_MODULE,'controller'=>CURRENT_CONTROLLER,'action'=>CURRENT_ACTION),
            'method' => Request::method(),
            'params' => array_merge($params, Request::gets(),Request::posts()),
            'stream' => Request::stream(),
            'cookie' => Request::cookies(),
            'ip' => Request::ip(),
        );
        C::log($log);
        if (!class_exists($className)) {
            throw new \RuntimeException('类不存在');
        }
        try{
            $obj = new \ReflectionClass($className);
            
            if ($obj->isAbstract()) {
                throw new \RuntimeException('抽象类不可被实例化');
            }
            
            $class = $obj->newInstance();
            
            //前置操作
            if ($obj->hasMethod(CURRENT_ACTION.'Before')) {
                $beforeMethod = $obj->getMethod(CURRENT_ACTION.'Before');
                if ($beforeMethod->isPublic() && !$beforeMethod->isStatic()) {
                    $beforeMethod->invoke($class);
                }
            }
            
            $method = $obj->getMethod(CURRENT_ACTION.'Action');
            if ($method->isPublic() && !$method->isStatic()) {
                $method->invoke($class);
            }
            
            //后置操作
            if ($obj->hasMethod(CURRENT_ACTION.'After')) {
                $afterMethod = $obj->getMethod(CURRENT_ACTION.'After');
                if ($afterMethod->isPublic() && !$afterMethod->isStatic()) {
                    $afterMethod->invoke($class);
                }
            }
        } catch (\Exception $e) {
            self::_halt($e);
        }
    }
    
    public static function errorHandler($errno, $errstr, $errfile, $errline){
        throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
    
    public static function exceptionHandler($e){
        self::$_error = $e;
    }
    
    public static function end(){
        if (self::$_error) {
            $e = self::$_error;
            self::$_error = null;
            self::_halt($e);
        }
        //输出日志
        $log = array(
            'response' => Response::getResData(),
            'type' => Response::getResType(),
            'request_id' => REQUEST_ID,
            'run time' => number_format((microtime(true) - self::$_startTime) * 1000, 0).'ms',
            'run memory' => number_format( (memory_get_usage(true) - self::$_memoryStart) / (1024), 0, ",", "." ).'kb'
        );
        C::log($log);
    }
    
    private static function _halt($e){
        if (DEBUG) {
            if(IS_CLI){
                exit(iconv('UTF-8','gbk',$e->getMessage()).PHP_EOL.'FILE: '.$e->getFile().'('.$e->getLine().')'.PHP_EOL.$e->getTraceAsString());
            }
            include_once ONEFOX_PATH.DS.'Tpl'.DS.'excetion.html';
        } else {
            $log_info['url'] = $_SERVER['REQUEST_URI'];
            $log_info['errmsg'] = $e->getMessage();
            $log_info['file'] = $e->getFile();
            $log_info['line'] = $e->getLine();
            C::log($log_info, Log::ERROR);//记录错误日志
            if (IS_CLI) {
                exit();
            }
            $url = Config::get('404_page');
            if ($url) {
                Response::redirect($url);
            }
            header('HTTP/1.1 404 Not Found');
            header('Status:404 Not Found');
            include_once ONEFOX_PATH.DS.'Tpl'.DS.'404.html';
        }
    }
}

Onefox::start();
