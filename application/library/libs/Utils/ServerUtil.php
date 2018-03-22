<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/6/6
 * Time: 下午2:40
 */

namespace libs\Utils;

/**
 * Class ServerUtil
 * 跟PHP/PHP-FPM/WEBSERVER相关的信息工具类.
 * @package libs\Utils
 */
class ServerUtil
{
    private static $_startTimer = 0;

    private static $_msg = [];

    private static $_globalMsg = '';

    /**
     * 获取当前Url地址
     * @return string
     */
    public static function url()
    {
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        return $http_type . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
    }

    /**
     * 获取访问的用户IP
     * @return string
     */
    public static function requestIp()
    {
        $onlineIp = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $onlineIp = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $onlineIp = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('REMOTE_ADDR')) {
            $onlineIp = getenv('REMOTE_ADDR');
        } else {
            $onlineIp = $_SERVER['REMOTE_ADDR'];
        }
        return $onlineIp;
    }

    /**
     * 获取所有请求headers
     * @return array
     */
    public static function requestHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }
        return $headers;
    }

    /**
     * 获取本地外网IP
     * @return mixed
     */
    public static function serverIp()
    {
        return $_SERVER['SERVER_ADDR'];
    }


    /**
     * 获取访问的HTTP BODY数据
     * @return string
     */
    public static function serverRawData()
    {
        return file_get_contents('php://input');
    }

    /**
     * 获取当前请求的服务器host
     * @return string
     */
    public static function serverHost()
    {
        $host = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
        return $host;
    }

    /**
     * 启动检测
     */
    public static function start()
    {
        self::$_startTimer = microtime(true);
    }

    /**
     * 获取启动检测到目前的毫秒数
     */
    public static function timer()
    {
        if (!self::$_startTimer) {
            return -1;
        }
        return (int)((microtime(true) - self::$_startTimer) / 1000);
    }

    /**
     * 常規接口類輸出
     * @param $isSuccess bool
     * @param null $data
     * @param string $msg
     * @param int $code
     */
    public static function output($data = null, $msg = '', $code = 0)
    {
        if (!$code) {
            echo json_encode([
                'code' => 0,
                'msg' => $msg,
                'data' => $data,
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'code' => $code,
                'msg' => $msg ? $msg : '未知错误',
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * 返回标准输出json
     * @param null $data
     * @param string $msg
     * @param int $code
     * @return string
     */
    public static function outputJson($data = null, $msg = '', $code = 0)
    {
        if (!$code) {
            return [
                'code' => 0,
                'msg' => $msg,
                'data' => $data,
            ];
        } else {
            return [
                'code' => $code,
                'msg' => $msg ? $msg : '未知错误',
            ];
        }
    }

    public static function set($key, $value)
    {
        if ($key) {
            self::$_msg[$key] = $value;
        }
    }

    public static function get($key, $default = '')
    {
        return Arrays::sGet(self::$_msg, $key, $default);
    }

    public static function setMsg($value)
    {
        if (!self::$_globalMsg) {
            self::$_globalMsg = uniqid();
        }
        self::set(self::$_globalMsg, $value);
    }

    public static function getMsg()
    {
        if (self::$_globalMsg) {
            return self::get(self::$_globalMsg);
        }
        return '';
    }
}