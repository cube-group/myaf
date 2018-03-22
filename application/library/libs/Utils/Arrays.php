<?php

namespace libs\Utils;

use libs\Orm\LActiveRecord;

/**
 * Class Arrays
 * @package libs\Utils
 */
class Arrays
{
    /**
     * 获取数组的值, 如果值不存在, 返回默认值
     *
     * @param array|object $array
     * @param $key
     * @param null $default 如果值为空返回默认值
     * @return mixed|null
     */
    public static function sGet($array, $key, $default = null)
    {
        $value = self::get($array, $key);
        return !empty($value) ? $value : $default;
    }


    /**
     * 获取数组的值
     *
     * @param array|object $array
     * @param $key
     * @param null $default 如果键不存在返回默认值
     * @return mixed|null
     */
    public static function get($array, $key, $default = null)
    {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::get($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::get($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            return $array->$key;
        } elseif (is_array($array)) {
            return array_key_exists($key, $array) ? $array[$key] : $default;
        } else {
            return $default;
        }
    }

    /**
     * 设置数组的值
     *
     * @param $array
     * @param $key
     * @param $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;

        return $array;
    }


    /**
     * 根据key重建数组索引
     *
     * @param $array
     * @param $key
     * @return array
     */
    public static function lists($array, $key)
    {
        $result = [];
        foreach ($array as $element) {
            $value = static::get($element, $key);
            $result[$value] = $element;
        }

        return $result;
    }


    /**
     * 取出数组的指定列
     *
     * @param $array
     * @param $name
     * @param bool $keepKeys
     * @return array
     */
    public static function column($array, $name, $keepKeys = true)
    {
        $result = [];
        if ($keepKeys) {
            foreach ($array as $k => $element) {
                $result[$k] = static::get($element, $name);
            }
        } else {
            foreach ($array as $element) {
                $result[] = static::get($element, $name);
            }
        }
        return $result;
    }


    /**
     * @param $object |array
     * @param array $properties
     * @param bool $recursive
     * @return array
     */
    public static function toArray($object, $properties = [], $recursive = true)
    {
        if (is_array($object)) {
            if ($recursive) {
                foreach ($object as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        $object[$key] = static::toArray($value, $properties, true);
                    }
                }
            }

            return $object;
        } elseif (is_object($object)) {
            if (!empty($properties)) {
                $className = get_class($object);
                if (!empty($properties[$className])) {
                    $result = [];
                    foreach ($properties[$className] as $key => $name) {
                        if (is_int($key)) {
                            $result[$name] = $object->$name;
                        } else {
                            $result[$key] = static::get($object, $name);
                        }
                    }

                    return $recursive ? static::toArray($result, $properties) : $result;
                }
            }
            if ($object instanceof LActiveRecord) {
                $result = $object->toArray();
            } else {
                $result = [];
                foreach ($object as $key => $value) {
                    $result[$key] = $value;
                }
            }

            return $recursive ? static::toArray($result) : $result;
        } else {
            return [$object];
        }
    }

    /**
     * 合并数组
     *
     * @param $a
     * @param $b
     * @return array|mixed
     */
    public static function merge($a, $b)
    {
        $args = func_get_args();
        $res = array_shift($args);
        while (!empty($args)) {
            $next = array_shift($args);
            foreach ($next as $k => $v) {
                if (is_int($k)) {
                    if (isset($res[$k])) {
                        $res[] = $v;
                    } else {
                        $res[$k] = $v;
                    }
                } elseif (is_array($v) && isset($res[$k]) && is_array($res[$k])) {
                    $res[$k] = self::merge($res[$k], $v);
                } else {
                    $res[$k] = $v;
                }
            }
        }

        return $res;
    }

    /**
     * 递归将数组中的键转为驼峰
     *
     * @param array $array 数组
     * @return array
     */
    public static function keyToCamel($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[Strings::case2camel($key)] = self::keyToCamel($value);
            } else {
                $result[Strings::case2camel($key)] = $value;
            }
        }
        return $result;
    }

    /**
     * 递归将数组中的键转为小写下划线
     *
     * @param array $array 数组
     * @return array
     */
    public static function keyToCase($array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result[Strings::camel2case($key)] = self::keyToCase($value);
            } else {
                $result[Strings::camel2case($key)] = $value;
            }
        }
        return $result;
    }


    /**
     * 递归移除的数组中的指定字段
     *
     * @param array $array
     * @param string|array $removeKeys
     * @return array
     */
    public static function removeKeys($array, $removeKeys)
    {
        $result = [];
        if (!is_array($removeKeys)) {
            $removeKeys = [$removeKeys];
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::removeKeys($value, $removeKeys);
            }
            if (in_array($key, $removeKeys)) {
                continue;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }


    /**
     * 判断是否是关联数组
     *
     * @access public
     * @param  $array
     *
     * @return boolean
     */
    public static function isAssoc($array)
    {
        return (bool)count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * 判断是否是多维数组
     *
     * @access public
     * @param  $array
     *
     * @return boolean
     */
    public static function isMultidim($array)
    {
        if (!is_array($array)) {
            return false;
        }

        return (bool)count(array_filter($array, 'is_array'));
    }


}