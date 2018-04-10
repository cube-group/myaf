<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 2018/3/11
 * Time: 下午10:44
 */

namespace Base\Utils;

/**
 * 简单的类库日志工具
 * Class LResult
 * @package libs\Utils
 */
class LResult
{
    protected static $_result = [];

    public static function setResult($func, $type, $value = null)
    {
        if (!$value) {
            return;
        }
        $content = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        self::$_result[] = "Func:{$func} Type:{$type} Content:{$content}";
    }

    public static function getResult()
    {
        return join("\n", self::$_result);
    }
}