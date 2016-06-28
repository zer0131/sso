<?php

/** 
 * @author ryan<zer0131@vip.qq.com>
 * @desc 一些通用工具
 */

namespace OneFox;

class C {
    
    /**
     * 过滤特殊字符
     * @param string $str
     * @return string
     */
    public static function filterChars($str, $onlyCharacterBase = false){
        $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/_-0123456789';
        if ($onlyCharacterBase) {
            $base = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $left = trim($str, $base);
        if ($left === '') {
            return $str;
        } else {
            //$ret = str_replace($left, '', $str);
			return '';
        }
    }
    
    /**
     * 输出日志
     * @param string|array $msg
     * @param string $level
     * @param string $config
     */
    public static function log($msg, $level='info', $config='default'){
        return Log::instance($config)->save($msg, $level);
    }

    /**
     * 安全数组合并
     * @param type $ar1
     * @param type $ar2
     * @return type
     */
    public static function arrayMerge($ar1, $ar2){
        if (is_array($ar1) && is_array($ar2)) {
            return array_merge($ar1, $ar2);
        } elseif (is_array($ar1) && !is_array($ar2)) {
            return $ar1;
        } elseif (!is_array($ar1) && is_array($ar2)) {
            return $ar2;
        }
        return null;
    }

    /**
     * 迭代创建目录
     */ 
    public static function mkDirs($path, $mode=0777) {
        if (!is_dir($path)) {
            if (!self::mkDirs(dirname($path), $mode)) {
                return false;
            }
            if (!mkdir($path, $mode)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * 生成随机字符串
     * @param int $length
     * @param boolean $haschar
     * @return string
     */
    public static function genRandomKey($length=10,$haschar=true){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        if($haschar){
            $chars .= "!@#$%^&*()-_[]{}<>~`+=,.;:/?";//包含特殊字符
        }
        $randomKey = '';
        for($i=0;$i<$length;$i++){
            $randomKey .= $chars[mt_rand(1,strlen($chars)-1)];
        }
        return $randomKey;
    }
    
    /**
     * 加载文件
     * @param string $filePath
     * @return boolean
     */
    public static function loadFile($filePath) {
        if (file_exists($filePath)) {
            return include $filePath;
        }
        return null;
    }

	/**
     * 生成模板页面输出用的tree
     * @param list array 二维数组
     * @param pid int 父级编号
     * @parma level int 层级
     * @param html string html输出前缀
     */
    public static function htmlToTree($list, $pid = 0, $level = 1, $html = ' -- ') {
        $tree = array();
        foreach ($list as $v) {
            if ($v['parent_id'] == $pid) {
                $v['sort'] = $level;
                $v['html'] = '|' . str_repeat($html, $level);
                $tree[] = $v;
                $tree = array_merge($tree, self::htmlToTree($list, $v['id'], $level + 1, $html));
            }
        }
        return $tree;
    }

	/**
     * 二维数组转化为树形列表
     * @param data array
     */
    public static function dataToTree($data) {
        $items = array();
        foreach ($data as $val) {
            $items[$val['id']] = $val;
        }
        unset($data);
        $tree = array();
        foreach ($items as $item) {
            if (isset($items[$item['parent_id']])) {
                $items[$item['parent_id']]['son'][] = &$items[$item['id']];
            } else {
                $tree[] = &$items[$item['id']];
            }
        }
        return $tree;
    }
    
    /**
     * 兼容gzip解压
     * @param type $data
     * @param type $filename
     * @param type $error
     * @param type $maxlength
     * @return boolean
     */
    public static function gzdecode($data, &$filename = '', &$error = '', $maxlength = null){
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
            $error = "Not in GZIP format.";
            return null;  // Not GZIP format (See RFC 1952)
        }
        $method = ord(substr($data, 2, 1));  // Compression method
        $flags  = ord(substr($data, 3, 1));  // Flags
        if ($flags & 31 != $flags) {
            $error = "Reserved bits not allowed.";
            return null;
        }
        // NOTE: $mtime may be negative (PHP integer limitations)
        $mtime = unpack("V", substr($data, 4, 4));
        $mtime = $mtime[1];
        $xfl   = substr($data, 8, 1);
        $os    = substr($data, 8, 1);
        $headerlen = 10;
        $extralen  = 0;
        $extra     = "";
        if ($flags & 4) {
            // 2-byte length prefixed EXTRA data in header
            if ($len - $headerlen - 2 < 8) {
                return false;  // invalid
            }
            $extralen = unpack("v", substr($data, 8, 2));
            $extralen = $extralen[1];
            if ($len - $headerlen - 2 - $extralen < 8) {
                return false;  // invalid
            }
            $extra = substr($data, 10, $extralen);
            $headerlen += 2 + $extralen;
        }
        $filenamelen = 0;
        $filename = "";
        if ($flags & 8) {
            // C-style string
            if ($len - $headerlen - 1 < 8) {
                return false; // invalid
            }
            $filenamelen = strpos(substr($data, $headerlen), chr(0));
            if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {
                return false; // invalid
            }
            $filename = substr($data, $headerlen, $filenamelen);
            $headerlen += $filenamelen + 1;
        }
        $commentlen = 0;
        $comment = "";
        if ($flags & 16) {
            // C-style string COMMENT data in header
            if ($len - $headerlen - 1 < 8) {
                return false;    // invalid
            }
            $commentlen = strpos(substr($data, $headerlen), chr(0));
            if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {
                return false;    // Invalid header format
            }
            $comment = substr($data, $headerlen, $commentlen);
            $headerlen += $commentlen + 1;
        }
        $headercrc = "";
        if ($flags & 2) {
            // 2-bytes (lowest order) of CRC32 on header present
            if ($len - $headerlen - 2 < 8) {
                return false;    // invalid
            }
            $calccrc = crc32(substr($data, 0, $headerlen)) & 0xffff;
            $headercrc = unpack("v", substr($data, $headerlen, 2));
            $headercrc = $headercrc[1];
            if ($headercrc != $calccrc) {
                $error = "Header checksum failed.";
                return false;    // Bad header CRC
            }
            $headerlen += 2;
        }
        // GZIP FOOTER
        $datacrc = unpack("V", substr($data, -8, 4));
        $datacrc = sprintf('%u', $datacrc[1] & 0xFFFFFFFF);
        $isize = unpack("V", substr($data, -4));
        $isize = $isize[1];
        // decompression:
        $bodylen = $len - $headerlen - 8;
        if ($bodylen < 1) {
            // IMPLEMENTATION BUG!
            return null;
        }
        $body = substr($data, $headerlen, $bodylen);
        $data = "";
        if ($bodylen > 0) {
            switch ($method) {
                case 8:
                    // Currently the only supported compression method:
                    $data = gzinflate($body, $maxlength);
                    break;
                default:
                    $error = "Unknown compression method.";
                    return false;
            }
        }  // zero-byte body content is allowed
        // Verifiy CRC32
        $crc   = sprintf("%u", crc32($data));
        $crcOK = $crc == $datacrc;
        $lenOK = $isize == strlen($data);
        if (!$lenOK || !$crcOK) {
            $error = ( $lenOK ? '' : 'Length check FAILED. ') . ( $crcOK ? '' : 'Checksum FAILED.');
            return false;
        }
        return $data;
    }

    /**
     * 计算两个时间戳的时间差
     * @param $begin 开始时间戳
     * @param $end 结束时间戳
     * @param $returnStr 是否返回字符串
     * @return array|string
     */
    public static function timeDiff($begin, $end, $returnStr=true) {
        if ( $begin < $end ) {
            $starttime = $begin;
            $endtime = $end;
        } else {
            $starttime = $end;
            $endtime = $begin;
        }
        $timediff = $endtime - $starttime;
        $days = intval( $timediff / 86400 );
        $daysStr = $days?$days.'天':'';
        $remain = $timediff % 86400;
        $hours = intval( $remain / 3600 );
        $hoursStr = $hours?$hours.'小时':'';
        $remain = $remain % 3600;
        $mins = intval( $remain / 60 );
        $minsStr = $mins?$mins.'分钟':'';
        $secs = $remain % 60;
        $secsStr = $secs?$secs.'秒':'';
        if ($returnStr) {
            return $daysStr.$hoursStr.$minsStr.$secsStr;
        }
        return array( "day" => $days, "hour" => $hours, "min" => $mins, "sec" => $secs );
    }

    /**
     * 参数签名通用方法
     * @param p array 参数
     * @param signKey string 签名字符串
     * @return string
     */
    public static function sign($p, $signKey='2#!&70op#e') {
        $signStr = '';
        if (empty($p) || !is_array($p)) {
            return $signStr;
        }
        unset($p['sign']);
        unset($p['signType']);
        foreach ($p as $k => $v) {
            if ($v !== '') {
                $signStr .= "{$k}={$v}&";
            }
        }
        return md5($signStr.$signKey);
    }
}

