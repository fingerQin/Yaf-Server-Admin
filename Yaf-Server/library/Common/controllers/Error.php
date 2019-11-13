<?php
/**
 * 公用异常处理。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common\controllers;

use finger\Exception\FingerException;
use finger\Utils\YCore;
use finger\Utils\YLog;
use finger\Exception\ServiceException;

class Error extends \Common\controllers\Common
{
    /**
     * 也可通过$request->getException()获取到发生的异常
     */
    public function errorAction($exception)
    {
        $errCode = $exception->getCode();
        $errMsg  = $exception->getMessage();

        // [1] 参数验证错误
        if ($exception instanceof ServiceException) {
            // 排除正式环境不需要记录日志的错误码。
            if (!in_array($errCode, NO_RECORD_API_LIST) && YCore::appconfig('app.env') == ENV_PRO) {
                // ...... 不记录日志 ......
            } else {
                if ($errCode == STATUS_ERROR) {
                    YLog::log($exception->__toString(), 'errors', 'log');
                } else {
                    YLog::log($exception->__toString(), 'serviceErr', 'log');
                }
            }
        } else {
            $errCode    = STATUS_ERROR;
            $errMsg     = '服务器繁忙,请稍候重试';
            $logContent = $exception->getMessage() . "\n" . $exception->getTraceAsString();

            if ($exception instanceof \RedisException) { // Redis 的错误写一篇 Redis 特定的目录文件。
                YLog::log($logContent, 'redis', 'log');
            } elseif ($exception instanceof \PDOException) { // MySQL PDO 报错。
                YLog::log($logContent, 'pdo', 'log');
            } else if ($exception instanceof FingerException) {
                YLog::log($logContent, 'yaflib', 'log');
            } else {
                YLog::log($logContent, 'errors', 'log');
            }
        }

        // [2] 根据是不同的请求类型响应不的数据。
        if (defined('IS_API')) {
            $data = [
                'code' => $errCode,
                'msg'  => $errMsg
            ];
            YLog::writeApiResponseLog($data);
            echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            $this->end();
        } else if ($this->_request->isCli()) {
            echo $exception->__toString();
            $this->end();
        } else {
            $this->error("{$errMsg}", '', 0);
        }
        exit(0);
    }
}