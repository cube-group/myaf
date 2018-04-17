<?php

namespace Base\Utils;

/**
 * Class URLUtil.
 * URL地址处理工具类.
 */
class URLUtil
{
    /**
     * 将url地址http改为https
     * @param $url string
     * @return mixed|string
     */
    public static function toHttps($url)
    {
        if (!$url) {
            return '';
        }
        return str_replace('http://', 'https://', $url);
    }

    /**
     * 给URL地址追加get参数.
     *
     * @param $url string
     * @param $params array
     * @return bool|string
     */
    public static function addParameter($url, $params)
    {
        if (!$url || !CommonUtil::isURL($url)) {
            return $url;
        }
        if ($params) {
            if (is_array($params)) {
                $newParams = [];
                foreach ($params as $key => $item) {
                    $newParams[] = $key . '=' . $item;
                }
                $params = join('&', $newParams);
            }
            return $url . (strstr($url, '?') ? '&' : '?') . $params;
        }
        return $url;
    }


    /**
     * 根据URL地址获取query string.
     *
     * @param $url string
     * @return array|bool
     */
    public static function getParameters($url)
    {
        if (!$url || !LValidator::isUrl($url)) {
            return false;
        }
        $urlQuery = [];
        if ($query = parse_url($url)) {
            $query = explode('&', $query);
            foreach ($query as $item) {
                list($key, $value) = explode('=', $item);
                $urlQuery[$key] = $value;
            }
        }
        return $urlQuery;
    }
}
