<?php
/**
 * 日志封装。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Utils;

use Models\Log;

class YLog
{
    /**
     * 记录 API 接口请求日志。
     * 
     * @param  array  $params  请求参数。
     * @return void
     */
    public static function writeApiRequestLog($params)
    {
        ksort($params);
        $GLOBALS['Server-api'] = $params;
    }

    /**
     * 记录 API 接口响应的数据日志。
     * 
     * @param  string  $result  响应 JOSN 数据。
     * @return void
     */
    public static function writeApiResponseLog($result)
    {
        $requestLog = isset($GLOBALS['Server-api']) ? $GLOBALS['Server-api'] : [];
        $requestLog['_response_date'] = date('Y-m-d H:i:s', time());
        $log = [
            'request'  => $requestLog,
            'response' => $result
        ];
        unset($GLOBALS['Server-api']);
        self::log($log, 'apis', 'log');
    }

    /**
     * 写日志。
     * 
     * @param  string|array  $logContent    日志内容。
     * @param  string        $logDir        日志目录。如：bank
     * @param  string        $logFilename   日志文件名称。如：bind。生成文件的时候会在 bind 后面接上日期。如:bind-20171121.log
     * @param  bool          $isForceWrite  是否强制写入硬盘。默认值：false。设置为 true 则日志立即写入硬盘而不是等待析构函数回收再执行。
     *
     * @return void
     */
    public static function log($logContent, $logDir = '', $logFilename = '', $isForceWrite = false) 
    {
        $time    = time();
        $logTime = date('Y-m-d H:i:s', $time);
        if (!is_array($logContent)) {
            $serverIP   = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
            $clientIP   = YCore::ip();
            $logContent = [
                'ErrorTime' => $logTime,
                'ServerIP'  => $serverIP,
                'ClientIP'  => $clientIP,
                'content'   => $logContent
            ];
        }
        
        $logfile = date('Ymd', $time);
        if (strlen($logDir) > 0 && strlen($logFilename) > 0) {
            $logDir   = trim($logDir, '/');
            $logPath  = APP_PATH . '/logs/' . $logDir;
            \Utils\YDir::create($logPath);
            $logPath .= "/{$logFilename}-{$logfile}.log";
        } else {
            $logPath  = APP_PATH . '/logs/errors/';
            \Utils\YDir::create($logPath);
            $logPath  = $logPath . $logfile . '.log';
        }
        $logCtx = json_encode($logContent, JSON_UNESCAPED_UNICODE) . "\n\n";
        $logObj = \finger\Log::getInstance();
        $logObj->write($logCtx, $logPath, $isForceWrite);
    }
}