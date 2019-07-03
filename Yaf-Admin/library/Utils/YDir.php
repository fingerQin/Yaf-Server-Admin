<?php
/**
 * 目录或文件操作操作。
 * @author fingerQin
 * @date 2016-05-24
 */

namespace Utils;

class YDir
{
    /**
     * 转换目录下面的所有文件编码格式
     *
     * @param  string  $inCharset   原字符集
     * @param  string  $outCharset  目标字符集
     * @param  string  $dir         目录地址
     * @param  string  $fileexts    转换的文件格式
     * 
     * @return string 如果原字符集和目标字符集相同则返回false，否则为true
     */
    public static function iconv($inCharset, $outCharset, $dir, $fileexts = 'php|html|htm|shtml|shtm|js|txt|xml')
    {
        if ($inCharset == $outCharset) {
            return false;
        }
        $list = self::lists($dir);
        foreach ($list as $v) {
            if (pathinfo($v, PATHINFO_EXTENSION) == $fileexts && is_file($v)) {
                file_put_contents($v, iconv($inCharset, $outCharset, file_get_contents($v)));
            }
        }
        return true;
    }

    /**
     * 列出目录下所有文件
     *
     * @param  string  $path  路径
     * @param  string  $exts  扩展名
     * @param  array   $list  增加的文件列表
     * 
     * @return array 所有满足条件的文件
     */
    public static function lists($path, $exts = '', $list = [])
    {
        $path  = self::path($path);
        $files = glob($path . '*');
        foreach ($files as $v) {
            if (! $exts || pathinfo($v, PATHINFO_EXTENSION) == $exts) {
                $list[] = $v;
                if (is_dir($v)) {
                    $list = self::lists($v, $exts, $list);
                }
            }
        }
        return $list;
    }

    /**
     * 删除目录及目录下面的所有文件
     *
     * @param  string $dir 路径。
     * 
     * @return bool 如果成功则返回 TRUE,失败则返回 FALSE。
     */
    public static function delete($dir)
    {
        $dir = self::path($dir);
        if (!is_dir($dir)) {
            return FALSE;
        }
        $list = glob($dir . '*');
        foreach ($list as $v) {
            is_dir($v) ? self::delete($v) : @unlink($v);
        }
        return @rmdir($dir);
    }

    /**
     * 创建目录
     *
     * @param  string  $path  路径。
     * @param  string  $mode  属性。
     * 
     * @return string 如果已经存在则返回true,否则为flase。
     */
    public static function create($path, $mode = 0777)
    {
        if (is_dir($path)) {
            return TRUE;
        }
        $path = self::path($path);
        @mkdir($path, $mode, true);
        @chmod($path, $mode);
        return true;
    }

    /**
     * 转化 \ 为 /
     *
     * @param  string  $path 路径。
     * @return string 路径
     */
    public static function path($path)
    {
        $path = str_replace('\\', '/', $path);
        if (substr($path, - 1) != '/') {
            $path = $path . '/';
        }
        return $path;
    }

    /**
     * 拷贝目录及下面所有文件
     *
     * @param  string  $fromdir  原路径。
     * @param  string  $todir    目标路径。
     * @return string 如果目标路径不存在则返回false,否则为true。
     */
    public static function copy($fromdir, $todir)
    {
        $fromdir = self::path($fromdir);
        $todir   = self::path($todir);
        if (!is_dir($fromdir)) {
            return false;
        }
        if (!is_dir($todir)) {
            self::create($todir);
        }
        $list = glob($fromdir . '*');
        if (!empty($list)) {
            foreach ($list as $v) {
                $path = $todir . basename($v);
                if (is_dir($v)) {
                    self::copy($v, $path);
                } else {
                    copy($v, $path);
                    @chmod($path, 0777);
                }
            }
        }
        return true;
    }

    /**
     * 设置目录下面的所有文件的访问和修改时间
     *
     * @param  string  $path   路径。
     * @param  int     $mtime  修改时间。
     * @param  int     $atime  访问时间。
     * 
     * @return bool 不是目录时返回false，否则返回 true。
     */
    public static function touch($path, $mtime = 0, $atime = 0)
    {
        $time  = time();
        $mtime = $mtime ? $mtime : $time;
        $atime = $atime ? $atime : $time;
        if (!is_dir($path)) {
            return false;
        }
        $path = self::path($path);
        if (!is_dir($path)) {
            touch($path, $mtime, $atime);
        }
        $files = glob($path . '*');
        foreach ($files as $v) {
            is_dir($v) ? self::touch($v, $mtime, $atime) : touch($v, $mtime, $atime);
        }
        return true;
    }

    /**
     * 目录列表
     *
     * @param  string  $dir        路径。
     * @param  int     $parentid
     * @param  array   $dirs
     * @return array 返回目录列表。
     */
    public static function tree($dir, $parentid = 0, $dirs = [])
    {
        global $id;
        if ($parentid == 0) {
            $id = 0;
        }
        $list = glob($dir . '*');
        foreach ($list as $v) {
            if (is_dir($v)) {
                $id ++;
                $dirs[$id] = [
                    'id'       => $id,
                    'parentid' => $parentid,
                    'name'     => basename($v),
                    'dir'      => $v . '/'
                ];
                $dirs = self::tree($v . '/', $id, $dirs);
            }
        }
        return $dirs;
    }

    /**
     * 转换字节数为其他单位
     *
     * @param  string  $filesize  字节大小
     * @return string 返回大小
     */
    public static function sizecount($filesize)
    {
        if ($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        } elseif ($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        } elseif ($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        } else {
            $filesize = $filesize . ' Bytes';
        }
        return $filesize;
    }
}