<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/6/6
 * Time: 下午2:52
 */

namespace libs\Utils;

/**
 * Class XMLUtil.
 * XML/Array转换类
 * @package libs\Utils
 */
class XMLUtil
{
    /**
     * 数组转为XML
     * @param $data array
     * @param string $charset
     * @param string $root
     * @return string
     */
    public static function xmlEncode($data, $charset = 'utf-8', $root = 'root')
    {
        $xml = '<?xml version="1.0" encoding="' . $charset . '"?>';
        $xml .= "<{$root}>";
        $xml .= self::array_to_xml($data);
        $xml .= "</{$root}>";
        return $xml;
    }

    /**
     * XML转为数组
     * @param $xml string
     * @param string $root
     * @return array
     */
    public static function xmlDecode($xml, $root = 'root')
    {
        $search = '/<(' . $root . ')>(.*)<\/\s*?\\1\s*?>/s';
        $array = array();
        if (preg_match($search, $xml, $matches)) {
            $array = self::xml_to_array($matches[2]);
        }
        return $array;
    }


    /**
     * 将值中的CDATA去除.
     * @param $value string
     * @return string
     */
    public static function replaceCDATA($value)
    {
        if (!$value) {
            return $value;
        }
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = str_replace('<![CDATA[', '', $value[$key]);
                $value[$key] = str_replace(']]>', '', $value[$key]);
            }
            return $value;
        } else {
            $value = str_replace($value, '', '<![CDATA[');
            $value = str_replace($value, '', ']]>');
            return $value;
        }
    }

    private static function array_to_xml($array)
    {
        if (is_object($array)) {
            $array = get_object_vars($array);
        }
        $xml = '';
        foreach ($array as $key => $value) {
            $_tag = $key;
            $_id = null;
            if (is_numeric($key)) {
                $_tag = 'item';
                $_id = ' id="' . $key . '"';
            }
            $xml .= "<{$_tag}{$_id}>";
            $xml .= (is_array($value) || is_object($value)) ? self::array_to_xml($value) : htmlentities($value);
            $xml .= "</{$_tag}>";
        }
        return $xml;
    }

    private static function xml_to_array($xml)
    {
        $search = '/<(\w+)\s*?(?:[^\/>]*)\s*(?:\/>|>(.*?)<\/\s*?\\1\s*?>)/s';
        $array = array();
        if (preg_match_all($search, $xml, $matches)) {
            foreach ($matches[1] as $i => $key) {
                $value = $matches[2][$i];
                if (preg_match_all($search, $value, $_matches)) {
                    $array[$key] = self::xml_to_array($value);
                } else {
                    if ('ITEM' == strtoupper($key)) {
                        $array[] = html_entity_decode($value);
                    } else {
                        $array[$key] = html_entity_decode($value);
                    }
                }
            }
        }
        return $array;
    }
}