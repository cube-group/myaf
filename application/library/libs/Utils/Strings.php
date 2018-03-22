<?php

namespace libs\Utils;

/**
 * 字符串工具
 *
 * Class Strings
 * @package libs\Strings
 */
class Strings
{
    /**
     * 将小写下划线字符串转换为驼峰命名
     *
     * @param $string
     * @param string $delimiter 分隔符
     * @return mixed
     */
    public static function case2camel($string, $delimiter = '_')
    {
        $reg = '/' . $delimiter . '([a-z])/';
        return preg_replace_callback($reg, function ($mathes) use ($delimiter) {
            return trim(strtoupper($mathes[0]), $delimiter);
        }, $string);
    }

    /**
     * 将驼峰命名转换为小写下划线字符串
     *
     * @param $string
     * @param string $delimiter 分隔符
     * @return string
     */
    public static function camel2case($string, $delimiter = '_')
    {
        return ltrim(preg_replace_callback('/[A-Z]/', function ($mathes) use ($delimiter) {
            return $delimiter . strtolower($mathes[0]);
        }, $string), $delimiter);
    }

    /**
     * 创建随机数
     *
     * @param int $length
     * @return bool|string
     */
    public static function random($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }


    /**
     * 创建随机数
     *
     * @param int $length
     * @return bool|string
     */
    public static function randomNumber($length = 16)
    {
        $pool = '123456789';

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }

    /**
     * 生成唯一id
     * @return string
     */
    public static function uuid()
    {
        return md5(uniqid() . time());
    }


    /**
     * 获取搜索的多值,支持中文逗号/英文逗号/空格
     *
     * parseMultiValue("3,5,6,8")返回['3', '5', '6', '8']
     * parseMultiValue("3,5,6,8",'intval') 返回[3, 5, 6, 8]
     * parseMultiValue("3")返回"3"
     * parseMultiValue("3",'intval')返回3
     *
     * @param string $value 字符串
     * @param mixed $func 要递归处理的函数名
     * @return array|int
     */
    public static function parseMultiValue($value, $func = '')
    {
        $value = trim($value, ' ');
        $value = preg_replace('/\s+/', ',', $value);
        $value = str_replace('，', ',', $value);
        if (false !== strpos($value, ',')) {
            $value = array_unique(explode(',', $value));
        }
        if ($func) {
            $value = is_array($value) ? array_map($func, $value) : $func($value);
        }
        return $value;
    }
}