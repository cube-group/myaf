<?php

namespace libs\Utils;

/**
 * Class SerializeUtil
 * 根序列化相关的工具
 * @package libs\Utils
 */
class SerializeUtil
{

    /**
     * 符合序列化过的字符串结构
     */
    const MAYBE_SERIALIZE = [
        '/^s:(\d+):(.*)"(.*)";$/',
        '/^i:(.*);$/',
        '/^b:(.*);$/',
        '/^d:(.*);$/',
        '/^N;$/',
        '/^a:(\d+):(.*)}$/',
        '/^O:(\d+):(.*)}$/'
    ];

    /**
     * 字符串是否匹配可能为php序列化过的字符串
     * 如果匹配则进行反序列化
     * @param $value string
     * @return bool|mixed
     */
    public static function matchAndUnSerialize($value)
    {
        if (!$value) {
            return $value;
        }
        foreach (self::MAYBE_SERIALIZE as $item) {
            if (preg_match($item, $value, $m)) {
                return unserialize($value);
            }
        }
        return $value;
    }
}