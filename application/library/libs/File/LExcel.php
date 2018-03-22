<?php
/**
 * Created by PhpStorm.
 * User: linyang
 * Date: 17/3/8
 * Time: 下午8:41
 */

namespace libs\File;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class Excel.
 * PHPExcel进行简单封装.
 * @package libs\Excel
 */
class LExcel
{
    /**
     * 生成EXCEL最大的支持行数.
     */
    const MAX_CREATE_ROW = 50000;
    /**
     * 生成EXCEL最大的支持列数.
     * 例如从A0-ZZ0
     */
    const MAX_CREATE_COLUMN = 676;
    /**
     * 字母表长度.
     */
    const LETTER_COUNT = 26;
    /**
     * 字母表.
     */
    const LETTER = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    /**
     * create生成文件格式为Excel2007
     */
    const CREATE_EXCEL = 'Xlsx';
    /**
     * create生成文件格式为pdf
     */
    const CREATE_PDF = 'Dompdf';
    /**
     * create生成文件格式为csv
     */
    const CREATE_CSV = 'Csv';
    /**
     * create生成文件格式为html
     */
    const CREATE_HTML = 'Html';


    /**
     * 创建Excel/Csv/Html/Pdf.
     * $datas和$titles可为数组或者单实例(但是数量一定要一致)
     * 其中$datas的每一项必须为二维数组(横向为column纵向为row)
     * 详见test实例
     *
     *
     * @param $localFileName
     * @param $datas
     * @param string $titles
     * @param string $type
     * @return bool
     */
    public static function create($localFileName, $datas, $titles = 'sheet0', $type = self::CREATE_EXCEL)
    {
        try {
            if (!$localFileName || !$datas) {
                return false;
            }
            $types = [self::CREATE_EXCEL, self::CREATE_PDF, self::CREATE_CSV, self::CREATE_HTML];
            if (!in_array($type, $types)) {
                return false;
            }
            if (!is_array($titles)) {
                $titles = [$titles];
                $datas = [$datas];
            }
            if (count($datas) != count($titles)) {
                return false;
            }

            // Create new PHPExcel object
            $objPHPExcel = new Spreadsheet();
            // Set properties
            $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");

            foreach ($titles as $key => $title) {
                $workSheet = $objPHPExcel->createSheet($key);
                // Set title
                $workSheet->setTitle($title);
                // Set Data.
                foreach ($datas[$key] as $rowIndex => $row) {
                    $columnCount = count($row);
                    if ($columnCount > self::MAX_CREATE_COLUMN) {
                        array_splice($row, $columnCount);
                    }
                    foreach ($row as $columnIndex => $columnValue) {
                        $workSheet->setCellValue(self::getLetter($columnIndex) . ($rowIndex + 1), $columnValue);
                    }
                }
            }

            /**
             * $type
             * private static $writers = [
            'Xls' => Writer\Xls::class,
            'Xlsx' => Writer\Xlsx::class,
            'Ods' => Writer\Ods::class,
            'Csv' => Writer\Csv::class,
            'Html' => Writer\Html::class,
            'Tcpdf' => Writer\Pdf\Tcpdf::class,
            'Dompdf' => Writer\Pdf\Dompdf::class,
            'Mpdf' => Writer\Pdf\Mpdf::class,
            ];
             */
            $objWriter = IOFactory::createWriter($objPHPExcel, $type);
            $objWriter->save($localFileName);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 根据columnIndex获得标准Excel列号.
     * 例如:$columnIndex=1,则返回B
     * 例如:$columnIndex=26,则返回AA
     * @param $columnIndex
     * @return mixed|string
     */
    private static function getLetter($columnIndex)
    {
        if ($columnIndex < self::LETTER_COUNT) {
            return self::LETTER[$columnIndex];
        } else {
            $letter1 = self::LETTER[(int)($columnIndex / self::LETTER_COUNT) - 1];
            $letter2 = self::LETTER[($columnIndex % self::LETTER_COUNT)];
            return $letter1 . $letter2;
        }
    }

    /**
     * 读取Excel文件
     * @param $localFileName string 本地excel文件
     * @param array $sheets array
     * @return array|bool
     */
    public static function read($localFileName, $sheets = [0])
    {
        try {
            if (!$localFileName || !is_file($localFileName)) {
                return false;
            }

            $excelObject = IOFactory::load($localFileName);
            if (!$excelObject) {
                return false;
            }

            //获取array结构化数据.
            $cellStack = [];
            foreach ($sheets as $index) {
                $workSheetObject = $excelObject->getSheet($index);
                //末列字母值
                $columnString = $workSheetObject->getHighestColumn();
                //行数
                $rowCount = $workSheetObject->getHighestRow();

                //从第二行开始拿数据(首行为header) A1:AD1
                for ($i = 1; $i <= $rowCount; $i++) {
                    array_push($cellStack, $workSheetObject->rangeToArray('A' . $i . ':' . $columnString . $i)[0]);
                }
            }

            return $cellStack;
        } catch (\Exception $e) {
            return false;
        }
    }
}