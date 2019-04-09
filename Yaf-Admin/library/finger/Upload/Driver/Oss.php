<?php
/**
 * 封装阿里云 OSS 存储。
 * @author fingerQin
 * @date 2018-08-09
 */

namespace finger\Upload\Driver;

use Utils\YCore;
use finger\Alisdk\Oss\OssClient;
use finger\Alisdk\Oss\Core\OssException;

class Oss
{
    /**
     * 上传文件根目录
     *
     * @var string
     */
    private $rootPath;

    /**
     * 本地上传错误信息
     *
     * @var string
     */
    private $error = '';

    /**
     * 存储 OSS 配置。
     *
     * @var array
     */
    private static $config = [
        'access_key'    => '',  // ID
        'access_secret' => '',  // Secret
        'endpoint'      => '',  // endpoint
        'bucket'        => '',  // bucket
    ];

    /**
     * 构造函数
     */
    public function __construct($config = [])
    {
        self::$config = [
            'access_key'    => $config['oss']['access_key'],    // ID
            'access_secret' => $config['oss']['access_secret'], // Secret
            'endpoint'      => $config['oss']['endpoint'],      // endpoint
            'bucket'        => $config['oss']['bucket'],        // bucket
        ];
    }

    /**
     * 根据Config配置，得到一个OssClient实例
     *
     * @return OssClient 一个OssClient实例
     */
    public static function getOssClient()
    {
        $ossClient = null;
        try {
            $ossClient = new OssClient(
                self::$config['access_key'],
                self::$config['access_secret'],
                self::$config['endpoint'],
                false
            );
        } catch (OssException $e) {
            YCore::exception(STATUS_ERROR, __FUNCTION__ . 'creating OssClient instance: FAILED'.$e->getMessage());
        }
        return $ossClient;
    }

    public static function getBucketName()
    {
        return self::$config['bucket'];
    }

    /**
     * 检测上传根目录
     *
     * @param  string  $rootpath  根目录
     * @return bool  true-检测通过，false-检测失败
     */
    public function checkRootPath($rootpath)
    {
        $this->rootPath = $rootpath;
        return true;
    }

    /**
     * 检测上传目录
     *
     * @param  string  $savepath  上传目录
     * @return bool  检测结果，true-通过，false-失败
     */
    public function checkSavePath($savepath)
    {
        return true;
    }

    /**
     * 保存指定文件
     *
     * @param  array  $file     保存的文件信息
     * @param  bool   $replace  同名文件是否覆盖
     * @return bool  保存状态，true-成功，false-失败
     */
    public function save($file, $replace = true)
    {
        try {
            $filename   = ltrim($this->rootPath, "./") . $file['savepath'] . $file['savename'];
            $ossClient  = self::getOssClient();
            $bucketName = self::getBucketName();
            if (is_null($ossClient)) {
                YCore::exception(STATUS_SERVER_ERROR, 'creating OssClient instance: FAILED');
            }
            $ossClient->uploadFile($bucketName, $filename, $file['tmp_name']);
            return true;
        } catch (OssException $e) {
            $this->error = '文件上传保存错误';
            return false;
        }
    }

    /**
     * 创建目录
     *
     * @param  string  $savepath  要创建的穆里
     * @return bool 创建状态，true-成功，false-失败
     */
    public function mkdir($savepath)
    {
        return true;
    }

    /**
     * 获取最后一次上传错误信息
     *
     * @return  string  错误信息
     */
    public function getError()
    {
        return $this->error;
    }
}