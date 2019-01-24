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
        if (is_array($message)) {
            $this->log = $message;
            parent::__construct($message['stackTrace'], $code, $previous);
        } else {
            parent::__construct($message, $code, $previous);
        }
        $this->classNameAndMethod = $classNameAndMethod;
        $this->methodArgs         = $args;
    }

    /**
     * 重写 __toString()。
     * @return string
     */
    public function __toString()
    {
        $errLog  = "Error Message: {$this->message} \n";
        $errLog .= "Error Code: [{$this->code}] \n";
        $errLog .= "Class Name and Method: {$this->classNameAndMethod}\n";
        $errLog .= "Method Params:\n" . print_r($this->methodArgs, true) . "\n";
        $errLog .= "Stack trace:\n" . $this->getTraceAsString();
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
        if (!empty($this->log)) {
            $this->log['ErrorCode'] = $this->code;
            $this->log['Method']    = $this->classNameAndMethod;
            $this->log['Params']    = $this->methodArgs;
            return $this->log;
        } else {
            $serverIP = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
            $clientIP = YCore::ip();
            return [
                'Type'       => 'ServiceException',
                'ErrorCode'  => $this->code,
                'ServerIP'   => $serverIP,
                'ClientIP'   => $clientIP,
                'Method'     => $this->classNameAndMethod,
                'Params'     => $this->methodArgs,
                'ErrorFile'  => '',
                'ErrorLine'  => '',
                'ErrorMsg'   => $this->message,
                'ErrorNo'    => 0, 
                'stackTrace' => $this->getTraceAsString()
            ];
        }
    }
}