<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 2018/3/12
 * Time: 上午10:40
 */

namespace libs\File;

use Mpdf\Mpdf;

/**
 * html生成pdf
 * Class LPdf
 * @package libs\File
 */
class LPdf
{
    /**
     * 直接输出pdf
     */
    public static function outputOnline($html, $basePath = '', $tmpDir = '')
    {
        $mpdf = new Mpdf(['tempDir' => $tmpDir ? $tmpDir : sys_get_temp_dir()]);
        $mpdf->WriteHTML($html);
        $mpdf->basepath = 'http://localhost:63342/PHPBASE';
        $mpdf->Output();
    }

    /**
     * 生成pdf文件地址
     * @param $html
     * @param $filename
     * @param string $tmpDir
     * @return bool
     * @throws \Mpdf\MpdfException
     */
    public static function outputFile($html, $filename, $tmpDir = '')
    {
        $mpdf = new Mpdf(['tempDir' => $tmpDir ? $tmpDir : sys_get_temp_dir()]);
        $mpdf->WriteHTML($html);
        $mpdf->Output($filename);

        if (is_file($filename)) {
            return $filename;
        }
        return false;
    }
}