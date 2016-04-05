<?php

/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc 日志类
 */

namespace OneFox;

final class Log {

    private static $_instance = null;
    //默认配置
    private $_config = array(
        'ext' => 'log',
        'date_format' => 'Y-m-d H:i:s',
        'filename' => '',
        'log_path' => '',
        'prefix' => '',
        'log_level' => 'info',
    );
    //日志文件
    private $_logFile = '';
    //日志级别
    private $_logLevels = array(
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7
    );

    /**
     * 实例化类
     * @param string $conf_str 配置
     */ 
    public static function instance($conf_str='default') {
        if (!self::$_instance) {
            self::$_instance = new self($conf_str);
        }
        return self::$_instance;
    }
    
    public function __construct($conf_str) {
        $config = Config::get('log.'.$conf_str);
        if ($config) {
            $this->_config = array_merge($this->_config, $config);
        }
        $this->_setLogFile();
        if (file_exists($this->_logFile) && !is_writable($this->_logFile)) {
            throw new \RuntimeException('没有文件写入权限');
        }
    }

    /**
     * 写入日志
     * 日志级别(由低到高): debug->info->notice->warning->error->critical->alert->emergency
     * @param string|array $msg
     * @param string $level
     */ 
    public function save($msg, $level='info') {
        if (!$msg) {
            return false;
        }
        if ($this->_logLevels[$this->_config['log_level']] < $this->_logLevels[$level]) {
            return false;
        }
        if (!is_array($msg)) {
            $msg = array('msg'=>$msg);
        }
        $content = strtoupper($level).' '.$this->_getDate();
        foreach ($msg as $key => $val) {
            if (is_array($val)) {
                $val = json_encode($val);//数组转化成json输出
            }
            $content .= ' '.$key.'=['.$val.']';
        }
        $content .= PHP_EOL;
        return file_put_contents($this->_logFile, $content, FILE_APPEND);
    }

    /**
     * 获取时间格式
     */
    private function _getDate() {
        $originalTime = microtime(true);
        $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
        $date = new \DateTime(date('Y-m-d H:i:s.' . $micro, $originalTime));
        return $date->format($this->_config['date_format']);
    }

    /**
     * 设置存入文件
     */ 
    private function _setLogFile() {
        $log_dir = $this->_config['log_path'];
        if (!$log_dir) {
            $log_dir = LOG_PATH;
        }
        $log_dir = rtrim($log_dir, DS);
        C::mkDirs($log_dir);//创建目录
        if ($this->_config['filename']) {
            if (strpos($this->_config['filename'], '.log') !== false || strpos($this->_config['filename'], '.txt') !== false) {
                $this->_logFile = $log_dir . DS . $this->_config['filename'];
            } else {
                $this->_logFile = $log_dir . DS . $this->_config['filename'] . '.' . $this->_config['ext'];
            }
        } else {
            $this->_logFile = $log_dir . DS . $this->_config['prefix'] . date('Y-m-d') . '.' . $this->_config['ext'];
        }
    }

}
