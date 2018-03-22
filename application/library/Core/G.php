<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/7/3
 * Time: 上午11:23
 */

namespace Core;

use libs\Log\LLog;
use libs\Utils\HttpUtil;
use Yaf\Config\Ini;
use Yaf\Config_Abstract;
use Yaf\Loader;
use Yaf\Registry;

/**
 * Class G
 * 全局全配置
 * (核心类勿改)
 * @package Core
 */
final class G
{
    /**
     * 初始化全局配置.
     * @param $ini Ini|Config_Abstract
     * @return Ini|Config_Abstract
     */
    public static function init($ini)
    {
        date_default_timezone_set($ini->application->timezone);

        Registry::set(R::INI_CONF, $ini);
        Registry::set(R::RUNTIME_TIME, microtime(true));

        //local libs.
        $localLibPath = $ini->application->library;
        //global libs.
        $globalLibPath = $ini->common->path->library;
        //global vendor autoload
        $globalLibAutoload = $ini->common->path->autoload;
        if ($globalLibAutoload && is_file($globalLibAutoload)) {
            require $globalLibAutoload;
        }

        //include all library.
        $loader = Loader::getInstance($localLibPath ? $localLibPath : null, $globalLibPath ? $globalLibPath : null);
        //set local libs namespace
        if ($ini->application->libraryNamespace) {
            $loader->registerLocalNamespace(explode(',', $ini->application->libraryNamespace));
        }

        //php base LLog::init
        LLog::init(
            $ini->application->name,
            $ini->common->path->log,
            $ini->application->timezone,
            $ini != APP_MODE_PRODUCT
        );
    }

    /**
     * 获取业务级别的全局配置
     * @return Ini
     */
    public static function conf()
    {
        return Registry::get(R::INI_CONF);
    }

    /**
     * 追加INI配置到全局Registry配置中
     * @param $key string
     * @param $ini Ini
     * @return bool|Ini
     */
    public static function ini($key, $ini = null)
    {
        if ($ini) {
            return Registry::set($key, $ini);
        }
        return Registry::get($key);
    }

    /**
     * 设置或获取用户id
     * @param null $value
     * @return mixed
     */
    public static function userId($value = null)
    {
        if ($value) {
            return Registry::set(R::VALUE_USER_ID, $value);
        }
        return Registry::get(R::VALUE_USER_ID);
    }

    /**
     * 设置或获取用户信息
     * @param null $value
     * @return mixed
     */
    public static function userInfo($value = null)
    {
        if ($value) {
            return Registry::set(R::VALUE_USER_INFO, $value);
        }
        return Registry::get(R::VALUE_USER_INFO);
    }

    /**
     * 设置或获取Output标准msg
     * @param null $value
     * @return mixed
     */
    public static function msg($value = null)
    {
        if ($value) {
            return Registry::set(R::VALUE_MSG, $value);
        }
        return Registry::get(R::VALUE_MSG) ? Registry::get(R::VALUE_MSG) : '';
    }

    /**
     * 设置或获取Output标准code
     * @param null $value
     * @return mixed
     */
    public static function code($value = null)
    {
        if ($value) {
            return Registry::set(R::VALUE_CODE, $value);
        }
        return Registry::get(R::VALUE_CODE) ? Registry::get(R::VALUE_CODE) : 0;
    }

    /**
     * 设置或获取用户访问的虚拟路由
     * @param null $value
     * @return mixed|string
     */
    public static function route($value = null)
    {
        if ($value) {
            return Registry::set(R::VALUE_ROUTE, $value);
        }
        return Registry::get(R::VALUE_ROUTE) ? Registry::get(R::VALUE_ROUTE) : '';
    }

    /**
     * 返回执行的时间(单位:毫秒)
     * @return int|mixed
     */
    public static function runtime()
    {
        $time = Registry::get(R::RUNTIME_TIME);
        return $time ? (int)((microtime(true) - $time) * 1000) : 0;
    }

    /**
     * 设置全局变量
     * @param $key string
     * @param $value mixed
     * @return bool
     */
    public static function set($key, $value)
    {
        return Registry::set($key, $value);
    }

    /**
     * 获取全局变量
     * @param $key string
     * @return mixed
     */
    public static function get($key)
    {
        return Registry::get($key);
    }

    /**
     * 获得标准输出json字符串
     * @param null|array $data
     * @param $msg string
     * @param int $code
     * @return string
     */
    public static function json($data = null, $msg = '', $code = 0)
    {
        $out = [
            'msg' => G::msg() ? G::msg() : $msg,
            'code' => $code ? $code : G::code(),
        ];
        if ($data) {
            $out['data'] = $data;
        }
        return json_encode($out, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 直接执行文件下载
     * @param $fileName string
     * @return bool|int
     */
    public static function download($fileName)
    {
        if ($static = G::conf()->application->static) {
            $fileName = realpath($static) . DIRECTORY_SEPARATOR . $fileName;
            if (is_file($fileName)) {
                header('Content-Type: ' . HttpUtil::getFileContentType($fileName));
                return readfile($fileName);
            }
        }
        return false;
    }

    /**
     * 刷新日志并且直接结束进程.
     * @param string $msg
     * @param $json bool 是否以标准json形式返回
     */
    public static function shutdown($msg = '', $json = true)
    {
        self::flush();
        exit($json ? self::json(null, $msg) : $msg);
    }


    /**
     * 立即返回echo缓存内容(但后续逻辑将会继续执行)
     * @param $msg string
     * @param $json bool
     * @return bool
     */
    public static function shutdownAndKeepAlive($msg = '', $json = true)
    {
        if (function_exists('fastcgi_finish_request')) {
            echo($json ? self::json(null, $msg) : $msg);
            if (fastcgi_finish_request()) {
                set_time_limit(0);
                return true;
            }
        }
        return false;
    }

    /**
     * 刷新日志缓冲区内容到本地文件.
     */
    public static function flush()
    {
        LLog::info('request', G::route(), 'time (ms) ' . G::runtime());
        LLog::flush();
    }
}