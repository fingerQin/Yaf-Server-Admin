<?php
/**
 * Office 之 Excel、Word、Power Point操作。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Utils;

use PHPExcel\Spreadsheet;

class YExcel
{
    /**
     * 26个大写字母。
     *
     * @var array
     */
    private static $alpha = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G',
        'H', 'I', 'J', 'K', 'L', 'M', 'N',
        'O', 'P', 'Q', 'R', 'S', 'T', 'U',
        'V', 'W', 'X', 'Y', 'Z'
    ];

    /**
     * 创建一个Excel。
     *
     * @param  array   $headerTitle  Excel第一行标题。
     * @param  array   $data         Excel 每行的数据。
     * @param  array   $savePath     保存路径。
     * @param  string  $filename     导出的文件名称。
     * @return void
     */
    public static function createExcel($headerTitle, $data, $savePath, $filename)
    {
        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->getProperties()->setCreator("winerQin")->setLastModifiedBy("winerQin")->setTitle($filename)->setSubject($filename)->setDescription($filename)->setKeywords()->setCategory();
        $heaerOffset = 0; // 标题对应的字母。
        $alphaRepeat = 0; // 字母重复次数。0代表未重复。
        $yPosition   = 1; // Y轴数字。标题只能位于第一列。
        $ojbSheet    = $objPHPExcel->setActiveSheetIndex(0);
        foreach ($headerTitle as $key => $title) {
            if ($heaerOffset == 26) {
                $heaerOffset = 0;
                $alphaRepeat += 1;
            }
            $secondAlpha = self::$alpha[$heaerOffset]; // 每列组成如：A-Z,AA-ZZ。此值是第二位。
            $firstAlpha  = ($alphaRepeat > 0) ? self::$alpha[$alphaRepeat - 1] : ''; // 此值是第一位。
            $ojbSheet->setCellValue("{$firstAlpha}{$secondAlpha}{$yPosition}", $title);
            $heaerOffset += 1;
        }
        $yPosition = 2; // Y轴数字。数据从第二列开始。
        foreach ($data as $line) {
            $heaerOffset = 0; // 每行对应的字母。
            $alphaRepeat = 0; // 字母重复次数。0代表未重复。
            foreach ($line as $cell) {
                if ($heaerOffset == 26) {
                    $heaerOffset  = 0;
                    $alphaRepeat += 1;
                }
                $secondAlpha = self::$alpha[$heaerOffset]; // 每列组成如：A-Z,AA-ZZ。此值是第二位。
                $firstAlpha  = ($alphaRepeat > 0) ? self::$alpha[$alphaRepeat - 1] : ''; // 此值是第一位。
                $ojbSheet->setCellValue("{$firstAlpha}{$secondAlpha}{$yPosition}", $cell);
                $heaerOffset += 1;
            }
            $yPosition += 1;
        }
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = \PHPExcel\IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $save_name = rtrim($savePath, '/\\') . DIRECTORY_SEPARATOR . $filename . '.xlsx';
        $objWriter->save($save_name);
    }

    /**
     * Excel导出[直接向浏览器输出一个Excel文件]。
     *
     * @param  array   $headerTitle  Excel第一行标题。
     * @param  array   $data         Excel每行的数据。
     * @param  string  $filename     导出的文件名称。
     * @return void
     */
    public static function excelExport($headerTitle, $data, $filename = '')
    {
        $objPHPExcel = new Spreadsheet();
        $objPHPExcel->getProperties()->setCreator("winerQin")->setLastModifiedBy("winerQin")->setTitle($filename)->setSubject($filename)->setDescription($filename)->setKeywords()->setCategory();
        $heaerOffset = 0; // 标题对应的字母。
        $alphaRepeat = 0; // 字母重复次数。0代表未重复。
        $yPosition   = 1; // Y轴数字。标题只能位于第一列。
        $ojbSheet    = $objPHPExcel->setActiveSheetIndex(0);
        foreach ($headerTitle as $key => $title) {
            if ($heaerOffset == 26) {
                $heaerOffset = 0;
                $alphaRepeat += 1;
            }
            $secondAlpha = self::$alpha[$heaerOffset]; // 每列组成如：A-Z,AA-ZZ。此值是第二位。
            $firstAlpha  = ($alphaRepeat > 0) ? self::$alpha[$alphaRepeat - 1] : ''; // 此值是第一位。
            $ojbSheet->setCellValue("{$firstAlpha}{$secondAlpha}{$yPosition}", $title);
            $heaerOffset += 1;
        }
        $yPosition = 2; // Y轴数字。数据从第二列开始。
        foreach ($data as $line) {
            $heaerOffset = 0; // 每行对应的字母。
            $alphaRepeat = 0; // 字母重复次数。0代表未重复。
            foreach ($line as $cell) {
                if ($heaerOffset == 26) {
                    $heaerOffset  = 0;
                    $alphaRepeat += 1;
                }
                $secondAlpha = self::$alpha[$heaerOffset]; // 每列组成如：A-Z,AA-ZZ。此值是第二位。
                $firstAlpha  = ($alphaRepeat > 0) ? self::$alpha[$alphaRepeat - 1] : ''; // 此值是第一位。
                $ojbSheet->setCellValue("{$firstAlpha}{$secondAlpha}{$yPosition}", $cell);
                $heaerOffset += 1;
            }
            $yPosition += 1;
        }
        $objPHPExcel->setActiveSheetIndex(0);
        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel\IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
    }

    /**
     * 导入Excel文件[获取Excel内容]。
     *
     * @param  string  $filename  文件名称。
     * @return array
     */
    public static function excelImport($filename)
    {
        $objPHPExcel   = \PHPExcel\IOFactory::load($filename);
        $sheet         = $objPHPExcel->getSheet(0);                                     // 读取第一個工作表
        $highestRow    = $sheet->getHighestRow();                                  // 取得总行数
        $highestColumm = $sheet->getHighestColumn();                            // 取得总列数
        $highestColumm = \PHPExcel\Cell::columnIndexFromString($highestColumm); // 字母列转换为数字列 如:AA变为27
        $result        = []; // 保存Excel表数据。
        // 循环读取每个单元格的数据。
        for ($row = 1; $row <= $highestRow; $row++) { // 行数是以第1行开始
            $sheetRow = [];
            for ($column = 0; $column < $highestColumm; $column++) { // 列数是以第0列开始
                // $columnName = \PHPExcel\Cell::stringFromColumnIndex($column);
                $sheetRow[] = $sheet->getCellByColumnAndRow($column, $row)->getValue();
            }
            $result[] = $sheetRow;
        }
        return $result;
    }
}