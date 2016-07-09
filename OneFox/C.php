<?php

/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc 一些通用工具
 */

namespace OneFox;

class C {

    private static $_classObj = array();

    /**
     * @param $str
     * @param bool $onlyCharacterBase
     * @return string
     */
    public static function filterChars($str, $onlyCharacterBase = false) {
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
     * @return object
     */
    public static function log($msg, $level = 'info', $config = 'default') {
        return Log::instance($config)->save($msg, $level);
    }

    /**
     * 安全数组合并
     * @param array $ar1
     * @param array $ar2
     * @return array|null
     */
    public static function arrayMerge($ar1, $ar2) {
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
    public static function mkDirs($path, $mode = 0777) {
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
    public static function genRandomKey($length = 10, $haschar = true) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        if ($haschar) {
            $chars .= "!@#$%^&*()-_[]{}<>~`+=,.;:/?";//包含特殊字符
        }
        $randomKey = '';
        for ($i = 0; $i < $length; $i++) {
            $randomKey .= $chars[mt_rand(1, strlen($chars) - 1)];
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
     * @param array $list 二维数组
     * @param int $pid 父级编号
     * @parma int $level 层级
     * @param string $html html输出前缀
     * @return array
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
     * @param array $data
     * @return array
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
     * @param string $data
     * @param string $filename
     * @param string $error
     * @param int $maxlength
     * @return boolean
     */
    public static function gzdecode($data, &$filename = '', &$error = '', $maxlength = null) {
        $len = strlen($data);
        if ($len < 18 || strcmp(substr($data, 0, 2), "\x1f\x8b")) {
            $error = "Not in GZIP format.";
            return null;  // Not GZIP format (See RFC 1952)
        }
        $method = ord(substr($data, 2, 1));  // Compression method
        $flags = ord(substr($data, 3, 1));  // Flags
        if ($flags & 31 != $flags) {
            $error = "Reserved bits not allowed.";
            return null;
        }
        // NOTE: $mtime may be negative (PHP integer limitations)
        $mtime = unpack("V", substr($data, 4, 4));
        $mtime = $mtime[1];
        $xfl = substr($data, 8, 1);
        $os = substr($data, 8, 1);
        $headerlen = 10;
        $extralen = 0;
        $extra = "";
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
        $crc = sprintf("%u", crc32($data));
        $crcOK = $crc == $datacrc;
        $lenOK = $isize == strlen($data);
        if (!$lenOK || !$crcOK) {
            $error = ($lenOK ? '' : 'Length check FAILED. ') . ($crcOK ? '' : 'Checksum FAILED.');
            return false;
        }
        return $data;
    }

    /**
     * 计算两个时间戳的时间差
     * @param int $begin 开始时间戳
     * @param int $end 结束时间戳
     * @param boolean $returnStr 是否返回字符串
     * @return array|string
     */
    public static function timeDiff($begin, $end, $returnStr = true) {
        if ($begin < $end) {
            $starttime = $begin;
            $endtime = $end;
        } else {
            $starttime = $end;
            $endtime = $begin;
        }
        $timediff = $endtime - $starttime;
        $days = intval($timediff / 86400);
        $daysStr = $days ? $days . '天' : '';
        $remain = $timediff % 86400;
        $hours = intval($remain / 3600);
        $hoursStr = $hours ? $hours . '小时' : '';
        $remain = $remain % 3600;
        $mins = intval($remain / 60);
        $minsStr = $mins ? $mins . '分钟' : '';
        $secs = $remain % 60;
        $secsStr = $secs ? $secs . '秒' : '';
        if ($returnStr) {
            return $daysStr . $hoursStr . $minsStr . $secsStr;
        }
        return array(
            "day" => $days,
            "hour" => $hours,
            "min" => $mins,
            "sec" => $secs
        );
    }


    /**
     * @param $p
     * @param string $signKey
     * @return string
     */
    public static function sign($p, $signKey = '2#!&70op#e') {
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
        return md5($signStr . $signKey);
    }

    /**
     * @param $className
     * @return mixed|null
     */
    public static function classFactory($className) {
        if (!$className) {
            return null;
        }
        if (!isset(self::$_classObj[$className]) && !self::$_classObj[$className] && class_exists($className)) {
            self::$_classObj[$className] = new $className;
        }
        return self::$_classObj[$className];
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public static function xml_encode($data, $root='onefox', $item='item', $attr='', $id='id', $encoding='utf-8') {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml   .= "<{$root}{$attr}>";
        $xml   .= self::data2Xml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }

    /**
     * 数据XML编码
     * @param mixed  $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id   数字索引key转换为的属性名
     * @return string
     */
    public static function data2Xml($data, $item='item', $id='id') {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if(is_numeric($key)){
                $id && $attr = " {$id}=\"{$key}\"";
                $key  = $item;
            }
            $xml    .=  "<{$key}{$attr}>";
            $xml    .=  (is_array($val) || is_object($val)) ? data_to_xml($val, $item, $id) : $val;
            $xml    .=  "</{$key}>";
        }
        return $xml;
    }

    /**
     * 数据库查询结果排序
     * 使用方法: C::sortDbRet($data, array('column_name'=>SORT_ASC));
     * @param $data
     * @param $columns
     * @return mixed
     */
    public static function sortDbRet($data, $columns) {
        $args = array();
        foreach ($columns as $k => $v) {
            $args[] = array_column($data, $k);
            $args[] = $v;
        }
        $args[] = &$data;
        call_user_func_array('array_multisort', $args);
        return array_pop($args);
    }
}

