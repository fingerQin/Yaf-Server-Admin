<?php
/**
 * 公用类库。
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Utils;

use Utils\YLog;
use finger\Validator;

class YCore
{
    /**
     * 抛出异常。
     * 
     * @param  int      $errCode            错误编号。
     * @param  string   $errMsg             错误信息。
     * @param  string   $classNameAndMethod 出错位置执行的类与方法。当使用 try cacth 捕获异常时将捕获的异常信息传入。
     * @param  string   $args               出错位置传入方法的参数。当使用 try cacth 捕获异常时将捕获的异常信息传入。
     * @throws \finger\ServiceException
     */
    public static function exception($errCode, $errMsg, $classNameAndMethod = '', $args = [])
    {
        if (strlen($classNameAndMethod) === 0) {
            // debug_backtrace() 返回整个堆栈调用信息。
            // 堆栈里面的第二个数组返回的是调用 YCore::exception() 方法所在的类与方法相关信息。
            $result             = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
            $classNameAndMethod = $result[1]['class'] . $result[1]['type'] . $result[1]['function'];
            $args               = $result[1]['args'];
        }
        throw new \finger\ServiceException($errMsg, $errCode, $classNameAndMethod, $args);
    }

    /**
     * 定义一个PHP set_error_handler 的错误回调函数。
     *
     * @param  int     $errno    错误的级别。
     * @param  string  $errstr   错误的信息。
     * @param  string  $errfile  发生错误的文件名。
     * @param  int     $errline  错误发生的行号。
     * @return void
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        // [1] 获取堆栈信息。
        $debugStack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
        $traceStack = '';
        foreach ($debugStack as $debug) {
            if (isset($debug['file'])) {
                $traceStack .= "#{$debug['file']} line {$debug['line']}\n";
            }
        }
        // [2] 根据环境控制错误信息输出。
        $serverIP = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
        $clientIP = YCore::ip();
        $trace    = "PHP Error:{$errno}\n"
                  . "ServerIP:{$serverIP}\n"
                  . "ClientIP:{$clientIP}\n"
                  . "Error Message:{$errstr}\n"
                  . "Error File:{$errfile}\n"
                  . "Error Line:{$errline}\n"
                  . "StackTrace:\n{$traceStack}";
        self::exception(STATUS_ERROR, $trace);
        exit(0);
    }

    /**
     * 定义一个PHP register_shutdown_function 回调方法。
     * 
     * --1) 当 PHP 发生语法级别错误时会调用该方法(已在 Yaf Bootstrap 中调用)。
     * --2) 每次 PHP 脚本执行结束判断是否存在语法错误。有就收集错误信息记录日志。
     * --3) 如果是 API 调用则返回 API 规定的 JSON 格式。
     * --4) 如果是非 API 调用则显示 HTTP status 500 错误。
     *
     * @return void
     */
    public static function registerShutdownFunction()
    {
        $errInfo = error_get_last();
        if (!empty($errInfo)) {
            // [1] 获取堆栈信息。
            $debugStack = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 4);
            $traceStack = '';
            foreach ($debugStack as $debug) {
                if (isset($debug['file'])) {
                    $traceStack .= "#{$debug['file']} line {$debug['line']}\n";
                }
            }
            // [2] 根据环境配置输出不同错误信息。
            $serverIP = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
            $clientIP = YCore::ip();
            $appDebug = self::appconfig('app.debug');
            $request  = new \Yaf_Request_Http();
            $isCli    = $request->isCli();
            $trace    = "PHP Error:{$errInfo['type']}\n"
                      . "ServerIP:{$serverIP}\n"
                      . "ClientIP:{$clientIP}\n"
                      . "Error Message:{$errInfo['message']}\n"
                      . "Error File:{$errInfo['file']}\n"
                      . "Error Line:{$errInfo['line']}\n"
                      . "StackTrace:\n{$traceStack}";
            YLog::log($trace, 'errors', 'log', YLog::LOG_TYPE_SYSTEM_ERROR);
            if (defined('IS_API')) {
                header("Access-Control-Allow-Origin: *");
                header('Content-type: application/json');
                $data = [
                    'code' => STATUS_ERROR,
                    'msg'  => $appDebug ? $trace : '服务器繁忙,请稍候重试'
                ];
                YLog::writeApiResponseLog($data);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } else if ($isCli) {
                $datetime = date('Y-m-d H:i:s', time());
                echo $datetime . "\n" . $trace;
            } else {
                if ($appDebug) {
                    echo $trace;
                } else {
                    header('HTTP/1.1 500 Internal Server Error');
                }
            }
        }
        exit(0);
    }

    /**
     * 根据两点间的经纬度计算距离
     * -- 1、纬度最大值为90度，经度最大值为180度。
     * -- 2、只要其中一个值为-1则返回0.这是特殊约定的业务逻辑。
     *
     * @param  float $lat  纬度值。
     * @param  float $lng  经度值。
     * @param  float $lat2 纬度值2。
     * @param  float $lng2 经度值2。
     * @return int
     */
    public static function distance($lat1, $lng1, $lat2, $lng2)
    {
        if ($lat1 == -1 || $lng1 == -1 || $lat2 == -1 || $lng2 == -1) {
            return 0;
        }
        $earthRadius = 6371000; // approximate radius of earth in meters
        $lat1          = ($lat1 * pi()) / 180;
        $lng1          = ($lng1 * pi()) / 180;
        $lat2          = ($lat2 * pi()) / 180;
        $lng2          = ($lng2 * pi()) / 180;
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude  = $lat2 - $lat1;
        $stepOne       = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo       = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }

    /**
     * 读取配置文件(config.ini)。
     * 
     * -- 先读取 .evn 文件，不存在，再读 config.ini 文件。
     *
     * @param  string  $key  配置名。
     * @param  string  $val  当值不存在返回此值。
     * @return mixed
     */
    public static function appconfig($key, $val = null)
    {
        // [1]
        $envConfigObj = null;
        $envConfigKey = 'envConfig';
        if (!\Yaf_Registry::has($envConfigKey)) {
            $envPath = APP_PATH . DIRECTORY_SEPARATOR . '.env';
            if (file_exists($envPath)) {
                $envConfigObj = new \Yaf_Config_Ini($envPath);
                \Yaf_Registry::set($envConfigKey, $envConfigObj);
                if (!is_null($envConfigObj[$key])) {
                    if (is_string($envConfigObj[$key])) {
                        return $envConfigObj[$key];
                    } else {
                        return $envConfigObj[$key]->toArray();
                    }
                }
            }
        } else {
            $envConfigObj = \Yaf_Registry::get($envConfigKey);
            if (!is_null($envConfigObj[$key])) {
                if (is_string($envConfigObj[$key])) {
                    return $envConfigObj[$key];
                } else {
                    return $envConfigObj[$key]->toArray();
                }
            }
        }
        // [2]
        $config = \Yaf_Registry::get('config');
        $cval   = $config->get($key);
        if (is_string($cval)) {
            return $cval;
        } else if (is_null($cval) === false) {
            return $cval->toArray();
        } else {
            return $val;
        }
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

    /**
     * 字符串加密、解密函数
     *
     * @param  string   $txt          字符串
     * @param  string   $operation    ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
     * @param  string   $key          密钥：数字、字母、下划线
     * @param  string   $expiry       过期时间
     * @return string
     */
    public static function sys_auth($string, $operation = 'ENCODE', $key = '', $expiry = 0)
    {
        $key_length    = 4;
        $key           = md5($key != '' ? $key : self::appconfig('app.key'));
        $fixedkey      = md5($key);
        $egiskeys      = md5(substr($fixedkey, 16, 16));
        $runtokey      = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), - $key_length) : substr($string, 0, $key_length)) : '';
        $keys          = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
        $string        = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $egiskeys), 0, 16) . $string : base64_decode(substr($string, $key_length));
        $i             = 0;
        $result        = '';
        $string_length = strlen($string);
        for ($i = 0; $i < $string_length; $i ++) {
            $result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
        }
        if ($operation == 'ENCODE') {
            return $runtokey . str_replace('=', '', base64_encode($result));
        } else {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $egiskeys), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        }
    }

    /**
     * 获取远程内容
     * 
     * -- 在使用类似方法或 CURL 的时候。如果确定不使用 IPV6 解析，请关闭它。
     *
     * @param  string $url     接口url地址
     * @param  int    $timeout 超时时间
     * @return string
     */
    public static function pc_file_get_contents($url, $timeout = 30)
    {
        $stream = stream_context_create([
            'http' => [
                'timeout' => $timeout
            ]
        ]);
        return @file_get_contents($url, 0, $stream);
    }

    /**
     * 获取请求ip
     *
     * @return string ip地址
     */
    public static function ip()
    {
        $ip = '127.0.0.1';
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches[0] : '';
    }

    /**
     * IE浏览器判断
     * @return bool
     */
    public static function is_ie()
    {
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if ((strpos($useragent, 'opera') !== false) || (strpos($useragent, 'konqueror') !== false)) {
            return false;
        }
        if (strpos($useragent, 'msie ') !== false) {
            return true;
        }
        return false;
    }

    /**
     * 递归计算一个数值。
     * @param  int $a 数值。
     * @return int
     */
    public static function factorial($a)
    {
        if ($a > 1) {
            $r = $a * self::factorial($a - 1);
        } else {
            $r = $a;
        }
        return $r;
    }

    /**
     * 获取身份证号对应的性别信息。
     *
     * @param  string  $idCardNo  身份证号。
     * @return void
     */
    public static function getIdCardNoSex($idCardNo)
    {
        if (strlen($idCardNo) === 0) {
            return User::SEX_UNKNOWN;
        }
        $sex = substr($idCardNo, 16, 1);
        return (($sex%2) == 1) ? User::SEX_MALE : User::SEX_FEMAIL;
    }

    /**
     * 获取身份证号对应的生日信息。
     *
     * @param  string  $idCardNo  身份证号。
     * @return string
     */
    public static function getIdCardNoBirthday($idCardNo)
    {
        if (strlen($idCardNo) === 0) {
            return null;
        }
        $year  = substr($idCardNo, 6, 4);
        $month = substr($idCardNo, 10, 2);
        $day   = substr($idCardNo, 12, 2);
        return "{$year}-{$month}-{$day}";
    }

    /**
     * 随机指定个数的整数范围值。
     * 
     * --1、范围段数值个数小于等于要取的个数的10倍，则直接使用shuffle方式获取。
     * --2、范围段数值个数大于要取的个数的10倍，则每个数值都随机产生。并去重。
     * --3、以上两点一是为了性能，二是为了能避免无效的随机值。
     *
     * @param  int  $min    范围最小值(含)。
     * @param  int  $max    范围小大值(含)。
     * @param  int  $count  要取的值个数。
     * 
     * @return array
     */
    public static function randomIntegerScope($min, $max, $count = 20)
    {
        $validCount = ($max - $min) + 1; // 包含边界值。所以，要加1。
        if ($validCount <= $count * 10) {
            $scopeVal = array_fill($min, $validCount, 0);
            $keys = array_keys($scopeVal);
            shuffle($keys);
            return array_slice($keys, 0, $count);
        } else {
            $randVals = [];
            while(true) {
                $randVal = mt_rand($min, $max);
                if (!in_array($randVal, $randVals)) {
                    $randVals[] = $randVal;
                    if (count($randVals) == $count) {
                        break;
                    }
                }
            }
            return $randVals;
        }
    }

    /**
     * 获取一个空对象。
     * 
     * @return object
     */
    public static function getNullObject()
    {
        return (object)[];
    }
}