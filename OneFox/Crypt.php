<?php

/**
 * @author ryan<zer0131@vip.qq.com>
 * @desc 加解密类
 */

namespace OneFox;

class Crypt {

    const CRYPT_KEY = '8Wm^Yi/h}s';

    /**
     * 加密
     * @param  string $defaultKey 关键密钥
     * @param  string $str 需加密的字串
     * @param int $expiry
     * @return string 加密后的加密字串
     */
    public static function encode($str,$expiry=0){
        return self::_cryptCode($str,"encode",$expiry);
    }

    /**
     * 解密
     * @param  string $defaultKey 关键密钥
     * @param  string $str 需解密的字串
     * @param  int $expiry
     * @return string 解密后的字串
     */
    public static function decode($str){
        return self::_cryptCode($str,"decode");
    }

    /**
     * 加密解密
     * @param  string $str 加密串
     * @param  string $operation 操作类型（机密或解密）	加密encode , 解密decode
     * @param int $expiry (0表示永久)
     * @param  string $defaultKey 关键密钥
     * @return string 加密或解密串
     */
    private static function _cryptCode($str, $operation = "decode", $expiry = 0, $defaultKey=''){
        if (empty($defaultKey)) {
            $defaultKey = self::CRYPT_KEY;
        }
        $ckeyLength = 4;
        $key = md5($defaultKey);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckeyLength ? ($operation == 'decode' ? substr($str, 0, $ckeyLength) : substr(md5(microtime()), -$ckeyLength)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $keyLength = strlen($cryptkey);

        if ($operation == 'decode') {
            $str = base64_decode(substr($str, $ckeyLength));
        } else {
            $str = sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($str . $keyb), 0, 16) . $str;
        }

        $strLength = strlen($str);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $keyLength]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $strLength; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($str[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'decode') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.base64_encode($result);
        }
    }
}
