<?php
/**
 * 公用异常处理。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Common\controllers;

use Utils\YCore;
use Utils\YLog;
use finger\ServiceException;

class Error extends \Common\controllers\Common
{
    private $errMsgTpl = [
        STATUS_FORBIDDEN            => '您没有权限访问',
        STATUS_NOT_FOUND            => '您访问的资源不存在',
        STATUS_SERVER_ERROR         => '服务器发生一个问题',
        STATUS_ERROR                => '服务器繁忙,请稍候重试',
        STATUS_LOGIN_TIMEOUT        => '登录超时,请重新登录',
        STATUS_NOT_LOGIN            => '您还未登录',
        STATUS_OTHER_LOGIN          => '您的账号在其他地方登录',
        STATUS_ALREADY_REGISTER     => '您的账号已经注册',
        STATUS_UNREGISTERD          => '您的账号还未注册',
        STATUS_PASSWORD_EDIT        => '您的密码已经修改,请重新登录',
        STATUS_METHOD_NOT_EXISTS    => '接口 method 参数未传递',
        STATUS_VERSION_NOT_EXISTS   => '接口 v 参数未传递',
        STATUS_APPID_NOT_EXISTS     => '接口 appid 参数未传递',
        STATUS_TIMESTAMP_NOT_EXISTS => '接口 timestamp 参数未传递',
        STATUS_IP_FORBID            => '受限 IP 不允许访问',
        STATUS_API_NOT_EXISTS       => '您的 APP 太旧请升级'
    ];

    /**
     * 也可通过$request->getException()获取到发生的异常
     */
    public function errorAction($exception)
    {
        $errCode = $exception->getCode();
        $errMsg  = $exception->getMessage();

        // [1] 参数验证错误
        if ($exception instanceof ServiceException) {
            // 排除不需要记录日志的错误码。
            if (YCore::appconfig('app.env') != ENV_PRO || !in_array($errCode, NO_RECORD_API_LIST)) {
                if ($errCode == STATUS_ERROR) {
                    YLog::log($exception->log(), 'errors', 'log');
                } else {
                    YLog::log($exception->log(), 'serviceErr', 'log');
                }
            }
        } else {
            $errCode = STATUS_ERROR;
            YLog::log($exception->getTraceAsString(), 'errors', 'log');
        }

        $errMsg = $this->getErrMsg($errCode, $errMsg);

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

    /**
     * 根据错误码取错误信息(输出给接口的消息)。
     *
     * -- 1、优先取当前类的 $errMsgTpl 错误字典。
     * -- 2、$errMsgTpl 错误字典取不到则读取方法异常抛出的错误消息。
     * 
     * @param  int  $errCode  错误码。
     *
     * @return string
     */
    private function getErrMsg($errCode, $errMsg)
    {
        if (array_key_exists($errCode, $this->errMsgTpl)) {
            return $this->errMsgTpl[$errCode];
        } else {
            return $errMsg;
        }
    }
}