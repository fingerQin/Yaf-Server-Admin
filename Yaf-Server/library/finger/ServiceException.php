<?php
/**
 * 系统业务异常类。
 * --1、此业务异常类要实现的目标是收集异常所处位置的类、方法、方法的参数、以及相应的 trace 信息。 
 *
 * @author fingerQin
 * @date 2017-05-27
 */

namespace finger;

use Utils\YCore;
use Utils\YUrl;

class ServiceException extends \Exception
{
    protected $classNameAndMethod = '';
    protected $methodArgs         = [];
    protected $log                = [];

    /**
     * 构造方法。
     * @param  string         $message            错误信息。
     * @param  int            $code               错误编码。
     * @param  string         $classNameAndMethod 类名与类方法。格式: User::register
     * @param  array          $args               方法参数。通过此参数可以记录到日志当中,定位问题可以反推现场。
     * @param  Exception|null $previous
     * @return void
     */
    public function __construct($message, $code = 0, $classNameAndMethod = '', $args = [], Exception $previous = null)
    {
        $code = intval($code);
        parent::__construct($message, $code, $previous);
        $this->classNameAndMethod = $classNameAndMethod;
        $this->methodArgs = $args;
    }

    /**
     * 重写 __toString()。
     * @return string
     */
    public function __toString()
    {
        $datetime = date('Y-m-d H:i:s');
        $errLog   = "ErrorTime:{$datetime} \n";
        $errLog  .= "ErrorMsg: {$this->message} \n";
        $errLog  .= "ErrorCode: [{$this->code}] \n";
        $errLog  .= "Method: {$this->classNameAndMethod}\n";
        $errLog  .= "Params:\n" . print_r($this->methodArgs, true) . "\n";
        $errLog  .= "StackTrace:\n" . $this->getTraceAsString();
        return $errLog;
    }

    /**
     * @return string
     */
    public function getClassNameAndMethod()
    {
        return $this->classNameAndMethod;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->methodArgs;
    }

    /**
     * 获取日志数据格式。
     *
     * @return void
     */
    public function log()
    {
        $requestUrl = YUrl::getUrl();
        $serverIP   = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        $clientIP   = YCore::ip();
        return [
            'ErrorType'  => 'ServiceException',
            'ErrorTime'  => date('Y-m-d H:i:s'),
            'ServerIP'   => $serverIP,
            'ClientIP'   => $clientIP,
            'RequestUrl' => $requestUrl,
            'Method'     => $this->classNameAndMethod,
            'Params'     => $this->methodArgs,
            'ErrorCode'  => $this->code,
            'ErrorMsg'   => $this->message,
            'StackTrace' => $this->getTraceAsString()
        ];
    }
}