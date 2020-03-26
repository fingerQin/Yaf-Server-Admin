<?php
/**
 * 公用异常处理。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common\controllers;

use finger\App;
use finger\Log;
use finger\Exception\FingerException;
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
            // 正式环境且在忽略的错误码列表中的错误不记录日志。
            if (App::getConfig('app.env') == ENV_PRO && in_array($errCode, NO_RECORD_API_LIST)) { 
                // 不记录日志。
            } else {
                $source     = ['source' => 'service'];
                $logContent = $exception->__toArray();
                $logContent = array_merge($source, $logContent);
                App::log($exception->__toString(), 'errors', 'log');
            }
        } else {
            $errCode    = STATUS_ERROR;
            $errMsg     = '服务器繁忙,请稍候重试';

            if ($exception instanceof \RedisException) { // Redis 的错误写一篇 Redis 特定的目录文件。
                $logContent = [
                    'source'      => 'redis',
                    'err_code'    => $exception->getCode(),
                    'err_msg'     => $exception->getMessage(),
                    'stack_trace' => $exception->getTraceAsString()
                ];
                App::log($logContent, 'errors', 'log');
            } elseif ($exception instanceof \PDOException) { // MySQL PDO 报错。
                $logContent = [
                    'source'      => 'pdo',
                    'err_code'    => $exception->getCode(),
                    'err_msg'     => $exception->getMessage(),
                    'stack_trace' => $exception->getTraceAsString()
                ];
                App::log($logContent, 'errors', 'log');
            } else if ($exception instanceof FingerException) {
                $logContent = $exception->__toArray();
                $logContent = array_merge(['source' => 'yaflib'], $logContent);
                App::log($logContent, 'errors', 'log');
            } else {
                $logContent = [
                    'source'      => 'other',
                    'err_code'    => $exception->getCode(),
                    'err_msg'     => $exception->getMessage(),
                    'stack_trace' => $exception->getTraceAsString()
                ];
                App::log($logContent, 'errors', 'log');
            }
        }

        // [2] 根据是不同的请求类型响应不的数据。
        if (defined('IS_API')) {
            $data = [
                'code' => $errCode,
                'msg'  => $errMsg
            ];
            Log::writeApiResponseLog($data);
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