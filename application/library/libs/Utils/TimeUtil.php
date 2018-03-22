<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/6/27
 * Time: 下午6:27
 */

namespace libs\Utils;

/**
 * Class TimeUtil
 * 时间相关工具类
 * @package libs\Utils
 */
class TimeUtil
{
    /**
     * 时间字符串转换为时间戳
     *
     * 例如: 20091227091010转换为秒级时间戳
     * @param $value string
     * @return int
     */
    public static function parseDateToStamp($value)
    {
        if (!$value || strlen($value) < 14) {
            return 0;
        }
        $timeStr = substr($value, 0, 4) . '-' . substr($value, 4, 2) . '-' . substr($value, 6, 2);
        $timeStr .= ' ' . substr($value, 8, 2) . ':' . substr($value, 10, 2) . ':' . substr($value, 12, 2);
        return strtotime($timeStr);
    }

    /**
     * 时间戳转为例如20091227091010
     *
     * @param $value int
     * @return int
     */
    public static function parseStampToDate($value)
    {
        if (!$value) {
            return '';
        }
        return date('YmdHis', $value);
    }
}