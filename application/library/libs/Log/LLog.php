<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/3/8
 * Time: 下午4:48
 */

namespace libs\Log;

use Exception;
use libs\File\LFile;

/**
 * Class LLog
 * @package libs\Log
 */
class LLog
{
    /**
     * 日志唯一id.
     */
    const LOG_HUNTER = 'fykcRequestHunter';


    /**
     * 是否为测试模式.
     * @var bool
     */
    private static $debug = false;
    /**
     * 本次http请求的唯一id.
     * @var string
     */
    private static $requestId = '';
    /**
     * 当前应用名称。
     * @var string
     */
    private static $app = '';
    /**
     * 日志存储目录.
     * @var string
     */
    private static $logPath = '';
    /**
     * 二维数组日志存储器.
     * @var array
     */
    private static $logs = [];

    /**
     * 开启将每次记录都写日志
     * @var bool
     */
    private static $autoFlush = false;

    /**
     * 初始化日志系统.
     *
     * @param $app string 当前应用名称
     * @param $path string 日志存储路径
     * @param $timeZone string 默认时区
     * @param $debug bool 是否为测试环境
     */
    public static function init($app, $path, $timeZone = 'Asia/Shanghai', $debug = false)
    {
        if (self::$app) {
            //只允许一次init
            return;
        }

        self::$app = $app;
        self::$debug = $debug;
        self::$logPath = $path;
        self::$requestId = self::getGlobalRequestId();

        date_default_timezone_set($timeZone);
    }

    /**
     * 设置自动刷日志
     *
     * @param bool $flag
     */
    public static function setAutoFlush($flag = false)
    {
        self::$autoFlush = $flag;
    }


    /**
     * 获取全局唯一请求id.
     * 如果未设置则从$_GET、$_POST参数中区搜索.
     * 如果还未找到则直接创建.
     *
     * @return string
     */
    public static function getGlobalRequestId()
    {
        if (!self::$requestId) {
            try {
                if (isset($_GET[self::LOG_HUNTER]) && $_GET[self::LOG_HUNTER]) {
                    self::$requestId = $_GET[self::LOG_HUNTER];
                } else if (isset($_POST[self::LOG_HUNTER]) && $_POST[self::LOG_HUNTER]) {
                    self::$requestId = $_POST[self::LOG_HUNTER];
                } else {
                    self::$requestId = uniqid();
                }
            } catch (Exception $e) {
                self::$requestId = uniqid();
            }
        }
        return self::$requestId;
    }


    /**
     * debug日志.
     *
     * @param string $name
     * @param $file string
     * @param array ...$args
     */
    public static function debug($name = 'default', $file = __FILE__, ...$args)
    {
        if (self::$debug) {
            self::append('DEBUG', $name, $args, $file);
        }
    }

    /**
     * 常规日志.
     *
     * @param string $name
     * @param $file
     * @param array ...$args
     */
    public static function info($name = 'default', $file = __FILE__, ...$args)
    {
        self::append('INFO', $name, $args, $file);
    }

    /**
     * 警告日志.
     *
     * @param string $name
     * @param $file string
     * @param array ...$args
     */
    public static function warn($name = 'default', $file = __FILE__, ...$args)
    {
        self::append('WARN', $name, $args, $file);
    }

    /**
     * 错误日志.
     *
     * @param string $name
     * @param $file string
     * @param array ...$args
     */
    public static function error($name = 'default', $file = __FILE__, ...$args)
    {
        self::append('ERROR', $name, $args, $file);
    }

    /**
     * 挂机错误日志.
     *
     * @param string $name
     * @param $file string
     * @param array ...$args
     */
    public static function fatal($name = 'default', $file = __FILE__, ...$args)
    {
        self::append('FATAL', $name, $args, $file);
    }


    /**
     * 将此次访问的的所有日志录入相关日志文件.
     * @return bool
     * @throws Exception
     */
    public static function flush()
    {
        if (empty(self::$logs)) {
            return true;
        }
        $logPath = self::$logPath;
        try {
            foreach (self::$logs as $childPath => $item) {
                $newLogPath = $logPath . '/' . $childPath;
                if (!is_dir($newLogPath)) {
                    if (!@mkdir($newLogPath, 0777, true)) {
                        continue;
                    }
                }
                $logFileName = $newLogPath . '/' . date('Y-m-d') . '.txt';
                $logContent = join("\n", $item) . "\n";
                if (is_file($logFileName)) {
                    LFile::append($logFileName, $logContent);
                } else {
                    LFile::create($logFileName, $logContent);
                }
            }
            self::$logs = [];
            return true;
        } catch (Exception $e) {
        }
        return false;
    }


    /**
     * 将日志压入内存暂存器.
     * @param $type string 日志等级
     * @param $dir string 子目录名称
     * @param $values array ...$args日志数组
     * @param $file string 打印日志所在的文件地址
     * @throws Exception
     */
    private static function append($type, $dir, $values, $file = __FILE__)
    {
        $dir = str_replace('/', '-', $dir);
        if (!self::$requestId || !self::$logPath) {
            throw new Exception('log not initialized');
        }
        if (!$dir || !$values) {
            throw new Exception('log not initialized');
        }
        if (!isset(self::$logs[$dir])) {
            self::$logs[$dir] = [];
        }
        $values = json_encode($values, JSON_UNESCAPED_UNICODE);

        $logString = self::getFormatString(date('Y-m-d H:i:s'));
        $logString .= self::getFormatString(getmypid());
        $logString .= self::getFormatString($file);
        $logString .= self::getFormatString($type);
        $logString .= '(' . self::$requestId . ')';
        $logString .= $values;

        array_push(self::$logs[$dir], $logString);

        if (self::$autoFlush) {
            self::flush();
        }
    }


    /**
     * 生成标准日志结构体.
     * @param $value string
     * @return string
     */
    private static function getFormatString($value)
    {
        return '[' . $value . ']';
    }
}