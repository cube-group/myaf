<?php

namespace Base\Auth;

use Base\Utils\Arrays;
use Base\Utils\CommonUtil;
use Base\Utils\URLUtil;

/**
 * Class LBasicAuth.
 * http参数基础验证类(仅基于秘钥).
 * @package Base\Auth
 */
class LBasicAuth
{
    /**
     * 秘钥.
     */
    private static $secret = '';

    /**
     * 初始化秘钥
     * @param $secret
     */
    public static function init($secret)
    {
        self::$secret = $secret;
    }

    /**
     * 基础验证是否通过.
     * @param mixed $data
     * @return bool
     */
    public static function check($sign, $data)
    {
        if (!$data) {
            return false;
        }

        return self::get($data) == $sign;
    }


    /**
     * 获取sign签名.
     * @param $data array|string 支持对数组|url string|query string的解析
     * @return string
     */
    public static function get($data)
    {
        if (!$data || (!is_string($data) && !is_array($data))) {
            return '';
        }

        $isString = false;
        $url = false;
        if (is_string($data)) {
            $isString = true;
            if (CommonUtil::isURL($data)) {
                $url = $data;
                $data = Arrays::get(parse_url($data), 'query', '');
            }
            $queryArr = [];
            parse_str($data, $queryArr);
            $data = $queryArr;
        }

        unset($data['sign']);
        ksort($data);
        if ($query = urldecode(http_build_query($data))) {
            $data = $query;
        }

        $sign = md5($data . '&secret=' . self::$secret);
        if ($url) {
            return URLUtil::addParameter($url, ['sign' => $sign]);
        } else if ($isString) {
            return $data . '&sign=' . $sign;
        } else {
            return $sign;
        }
    }

    /**
     * 进行参数的时间戳秘钥校验
     * @param $data array
     * @return bool
     */
    public static function timeStampTypeCheck($data)
    {
        if (!$data) {
            return false;
        }
        if (!isset($data['t']) || !isset($data['sign'])) {
            return false;
        }
        if (md5('t=' . $data['t'] . '&secret=' . self::$secret) != $data['sign']) {
            return false;
        }
        return true;
    }

    /**
     * 获取基于时间戳秘钥的sign签名
     * @return string
     */
    public static function timeStampTypeGet()
    {
        $t = time();
        return [
            't' => $t,
            'sign' => md5('t=' . $t . '&secret=' . self::$secret)
        ];
    }
}