<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 2017/11/20
 * Time: 上午10:21
 */

namespace Base\Utils;

/**
 * Class HttpUtil
 * @package Base\Utils
 */
class HttpUtil
{
    /**
     * 根据文件名称获取相关联的ContentType.
     * @param $filename string
     * @return string
     */
    public static function getFileContentType($filename)
    {
        $path_parts = pathinfo($filename);//返回文件路径的信息
        $ext = strtolower($path_parts["extension"]); //将字符串转化为小写
        // Determine Content Type
        $cType = '';
        switch ($ext) {
            case "ico":
                $cType = "image/x-icon";
                break;
            case "pdf":
                $cType = "application/pdf";
                break;
            case "exe":
                $cType = "application/octet-stream";
                break;
            case "zip":
                $cType = "application/zip";
                break;
            case "doc":
                $cType = "application/msword";
                break;
            case "xls":
            case "xlsx":
                $cType = "application/vnd.ms-excel";
                break;
            case "ppt":
                $cType = "application/vnd.ms-powerpoint";
                break;
            case "gif":
                $cType = "image/gif";
                break;
            case "png":
                $cType = "image/png";
                break;
            case "jpeg":
            case "jpg":
                $cType = "image/jpg";
                break;
            case "css":
                $cType = "text/css";
                break;
            case "js":
                $cType = "text/x-javascript";
                break;
            case "html":
                $cType = "text/html";
                break;
            case "txt":
            case "xml":
                $cType = "text/xml";
                break;
            default:
                $cType = "application/force-download";
                break;
        }

//        header("Pragma", "public"); // required 指明响应可被任何缓存保存
//        header("Expires", "0");
//        header("Cache-Control", "must-revalidate, post-check=0, pre-check=0");
//        header("Content-Type", $ctype);
        return $cType;
    }

    /**
     * 设置HTTP STATUS_CODE
     * @param $code int
     */
    public static function statusCode($code)
    {
        $status = [
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        ];
        if (array_key_exists($code, $status)) {
            header('HTTP/1.1 ' . $code . ' ' . $status[$code]);
        }
    }
}