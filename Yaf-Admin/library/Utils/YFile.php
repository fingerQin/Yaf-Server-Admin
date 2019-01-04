<?php
/**
 * 文件相关操作封装。
 * @author fingerQin
 * @date 2018-06-29
 */

namespace Utils;

class YFile
{
    /**
     * 取得文件扩展
     *
     * @param  string  $filename  文件名
     * @return 扩展名
     */
    public static function fileext($filename)
    {
        return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
    }

    /**
     * 文件下载
     *
     * @param  string  $filepath  文件路径
     * @param  string  $filename  文件名称
     * @return void
     */
    public static function file_down($filepath, $filename = '')
    {
        if (!$filename) {
            $filename = basename($filepath);
        }
        if (self::is_ie()) {
            $filename = rawurlencode($filename);
        }
        $filetype = self::fileext($filename);
        $filesize = sprintf("%u", filesize($filepath));
        if (ob_get_length() !== false) {
            @ob_end_clean();
        }
        header('Pragma: public');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Content-Transfer-Encoding: binary');
        header('Content-Encoding: none');
        header('Content-type: ' . $filetype);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-length: ' . $filesize);
        readfile($filepath);
        exit();
    }
}