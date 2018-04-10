<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/3/14
 * Time: 上午10:48
 */

namespace Base\Utils;

/**
 * Class DownloadHeader
 * @package Core
 */
class ContentType
{
    /**
     * 根据文件名称获取相关联的ContentType.
     * @param $filename
     * @return string
     */
    public static function getFileContentType($filename)
    {
        $path_parts = pathinfo($filename);//返回文件路径的信息
        $ext = strtolower($path_parts["extension"]); //将字符串转化为小写
        // Determine Content Type
        switch ($ext) {
            case "ico":
                $ctype = "image/x-icon";
                break;
            case "pdf":
                $ctype = "application/pdf";
                break;
            case "exe":
                $ctype = "application/octet-stream";
                break;
            case "zip":
                $ctype = "application/zip";
                break;
            case "doc":
                $ctype = "application/msword";
                break;
            case "xls":
            case "xlsx":
                $ctype = "application/vnd.ms-excel";
                break;
            case "ppt":
                $ctype = "application/vnd.ms-powerpoint";
                break;
            case "gif":
                $ctype = "image/gif";
                break;
            case "png":
                $ctype = "image/png";
                break;
            case "jpeg":
            case "jpg":
                $ctype = "image/jpg";
                break;
            case "css":
                $ctype = "text/css";
                break;
            case "js":
                $ctype = "text/x-javascript";
                break;
            case "html":
                $ctype = "text/html";
                break;
            case "txt":
            case "xml":
                $ctype = "text/xml";
                break;
            default:
                $ctype = "application/force-download";
                break;
        }

//        header("Pragma", "public"); // required 指明响应可被任何缓存保存
//        header("Expires", "0");
//        header("Cache-Control", "must-revalidate, post-check=0, pre-check=0");
//        header("Content-Type", $ctype);
        return $ctype;
    }
}