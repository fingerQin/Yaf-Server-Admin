<?php
/**
 * 上传管理。
 * @author fingerQin
 * @date 2018-07-08
 */

namespace Services\System;

use finger\Validator;
use Utils\YCore;
use Utils\YUrl;
use Utils\YDir;
use Models\Files;

class Upload extends \Services\AbstractBase
{
    const FILE_TYPE_IMAGE = 1; // 图片。
    const FILE_TYPE_OTHER = 2; // 其它格式文件。

    /**
     * 上传其它类型文件。
     *
     * @param  int     $userType   用户类型。1管理员、2普通用户。决定user_id的值。
     * @param  int     $userid     用户ID。如果是私有图片必须设置此值。如果是公开图片此值有就必传没有就不传。
     * @param  string  $dirname    目录名称。当保存图片的时候，会把相应的图片保存在此目录下。
     * @param  int     $fileSize   文件最大限制。单位（M）
     * @param  string  $inputFileName   File 表单上传控制的 name 值。
     *
     * @return array
     */
    public static function uploadOtherFile($userType = 2, $userid = 0, $dirname = '', $fileSize = 0, $inputFileName = 'image')
    {
        if ($fileSize <= 0) {
            YCore::exception(STATUS_SERVER_ERROR, 'The file_size parameter must be greater than zero');
        }
        if (!Validator::is_alpha_dash($dirname)) {
            YCore::exception(STATUS_SERVER_ERROR, 'The dirname parameter must be wrong');
        }
        if (!isset($_FILES[$inputFileName])) {
            YCore::exception(STATUS_SERVER_ERROR, '图片上传有误');
        }
        $allowExts = [
            'zip', 'rar', 'doc', 'docx', 'xls', 'xlsx', 'pptx', 'ppt', 'gz',
            'bz2', 'mp3', 'ogg', 'mp4', 'avi', 'flv', 'mpeg', 'wmv', 'mkv',
            '3gp', 'mpg', 'mpeg', 'rm', 'rmvb', 'vob', 'mov', 'amr', 'wav',
            'txt', 'pdf', 'dmg'
        ];
        $maxSize           = $fileSize * 1024 * 1024;
        $rootDir           = YCore::appconfig('upload.save_dir');
        $rootDir           = $rootDir ? realpath($rootDir) . DIRECTORY_SEPARATOR : ''; // 去除结尾处的目录分隔钱并重新拼接上当前运行系统的目录分隔线。
        $rootPath          = $rootDir . 'images/';
        $upload            = new \finger\Upload(); // 实例化上传类
        $upload->maxSize   = $maxSize;            // 设置附件上传大小
        $upload->exts      = $allowExts;          // 设置附件上传类型
        $upload->rootPath  = $rootPath;           // 设置附件上传根目录
        $upload->savePath  = $dirname . '/';      // 设置附件上传（子）目录
        $info              = $upload->uploadOne($_FILES[$inputFileName]);
        $fileinfo          = [];
        $FilesModel        = new Files();
        $fileName          = "/images/{$info['savepath']}{$info['savename']}";
        $imageUrl          = YUrl::filePath($fileName);
        $fileId            = $FilesModel->addFiles($imageUrl, 1, $info['size'], $info['md5'], $userType, $userid);
        if ($fileId == 0) {
            YCore::exception(STATUS_ERROR, '文件上传失败');
        }
        $fileinfo = [
            'file_id'            => $fileId,
            'image_url'          => $imageUrl,
            'relative_image_url' => $fileName
        ];
        return $fileinfo;
    }

    /**
     * 图片上传(只能上传一张图片)。
     * 
     * -- 上传图片时的 name=image
     *
     * @param  int     $userType       用户类型。1管理员、2普通用户。决定 userId 的值。
     * @param  int     $userId         用户ID。如果是私有图片必须设置此值。如果是公开图片此值有就必传没有就不传。
     * @param  string  $dirname        目录名称。当保存图片的时候，会把相应的图片保存在此目录下。
     * @param  int     $fileSize       文件最大限制。单位（M）
     * @param  string  $inputFileName  File 表单上传控制的 name 值。
     *
     * @return array
     */
    public static function uploadImage($userType = 2, $userId = 0, $dirname = '', $fileSize = 0, $inputFileName = 'image')
    {
        if ($fileSize <= 0) {
            YCore::exception(STATUS_SERVER_ERROR, 'The fileSize parameter must be greater than zero');
        }
        if (!Validator::is_alpha_dash($dirname)) {
            YCore::exception(STATUS_SERVER_ERROR, 'The dirname parameter must be wrong');
        }
        if (!isset($_FILES[$inputFileName])) {
            YCore::exception(STATUS_SERVER_ERROR, '图片上传有误');
        }
        $maxSize           = $fileSize * 1024 * 1024;
        $rootDir           = YCore::appconfig('upload.root_dir');
        $rootDir           = $rootDir ? realpath($rootDir) . DIRECTORY_SEPARATOR : ''; // 去除结尾处的目录分隔钱并重新拼接上当前运行系统的目录分隔线。
        $rootPath          = $rootDir . 'images/';
        $upload            = new \finger\Upload(); // 实例化上传类
        $upload->maxSize   = $maxSize;            // 设置附件上传大小
        $upload->exts      = ['jpg', 'gif', 'png', 'jpeg']; // 设置附件上传类型
        $upload->rootPath  = $rootPath;           // 设置附件上传根目录
        $upload->savePath  = $dirname . '/';      // 设置附件上传（子）目录
        $info              = $upload->uploadOne($_FILES[$inputFileName]);
        $fileinfo          = [];
        $FilesModel        = new Files();
        $fileName          = "/images/{$info['savepath']}{$info['savename']}";
        $imageUrl          = YUrl::filePath($fileName);
        $fileId            = $FilesModel->addFiles($imageUrl, 1, $info['size'], $info['md5'], $userType, $userId);
        if ($fileId == 0) {
            YCore::exception(STATUS_ERROR, '文件上传失败');
        }
        $fileinfo = [
            'file_id'            => $fileId,
            'image_url'          => $imageUrl,
            'relative_image_url' => $fileName
        ];
        return $fileinfo;
    }
}