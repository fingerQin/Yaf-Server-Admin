<?php
/**
 * 所有 API 接口基类。
 * 
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Apis;

use Utils\YCore;
use Utils\YInput;
use Models\ApiAuth;

abstract class AbstractApi
{
    /**
     * API 接口类型。
     * -- 其他类型可能自由增减,程序已经自动映射。独 app 特殊是允许公开访问。
     */
    const API_TYPE_APP = 'app'; // APP 调用的接口。

    /**
     * 请求参数。
     *
     * @var array
     */
    protected $params  = [];

    /**
     * API 接口类型。
     * 
     * -- 不同类型的接口有不同的作用。如单独提供给活动访问的接口。
     *
     * @var string
     */
    protected $apiType = null;

    /**
     * 结果。
     *
     * @var array
     */
    protected $result  = [];

    /**
     * API KEY。
     *
     * @var string
     */
    protected $apiKey = '';

    /**
     * API 密钥。
     * 
     * @var string
     */
    protected $apiSecret = '';

    /**
     * 构造方法。
     *
     * @param  array   $data       所有请求过来的参数。
     * @param  string  $apiType    API 接口类型。
     * @param  string  $apiKey     接口标识。
     * @param  string  $apiSecret  接口密钥。  
     *
     * -- 1、合并提交的参数。
     * -- 2、调用权限判断。
     * -- 3、签名验证。
     * -- 4、参数格式判断。
     * -- 5、运行接口逻辑。
     */
    public function __construct(&$data, $apiType = self::API_TYPE_APP, $apiKey = '', $apiSecret = '')
    {
        $this->apiType   = $apiType;
        $this->timestamp = $_SERVER['REQUEST_TIME'];
        $this->params    = $data;
        $this->apiKey    = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->checksignature();
        $ip     = YCore::ip();
        $status = $this->isApiIPInWhiteList($apiType, $ip);
        if (!$status) {
            YCore::exception(STATUS_SERVER_ERROR, '您没有权限访问');
        }
        $this->runService();
    }

    /**
     * 业务逻辑。
     *
     * -- 关于接口的逻辑写在此方法中。
     *
     * @return void
     */
    abstract protected function runService();

    /**
     * 验证码请求签名。
     *
     * @return boolean
     */
    protected function checksignature()
    {
        DecodeAdapter::checkSign($this->params, $this->apiSecret);
        $this->checkApiTypeAuth($this->apiType);
        return true;
    }

    /**
     * 检查当前接口类型与当前密钥对应的权限是否对应。
     * 
     * @param  string $appType 当前调用 APPID 对应的应用类型。 
     *
     * @return void
     */
    protected function checkApiTypeAuth($appType)
    {
        if (\strtolower($appType) != $this->apiType) {
            YCore::exception(STATUS_SERVER_ERROR, '您没有调用该接口的权限');
        }
    }

    /**
     * 数据返回格式统一组装方法。
     *
     * @param  int     $code   错误码，必须是int类型。
     * @param  string  $msg    提示信息。
     * @param  array   $data   数据。
     * @return void
     */
    public function render($code, $msg, $data = null)
    {
        if (!is_int($code)) {
            YCore::exception(STATUS_ERROR, 'BaseApi render method of code parameter must be an integer');
        }
        $this->result = [
            'code' => $code,
            'msg'  => $msg
        ];
        if ($code == 200 && ! is_null($data)) {
            $data = empty($data) ? (object)[] : $data;
            $this->result['data'] = $data;
        }
    }

    /**
     * 响应结果。
     *
     * @return string
     */
    public function renderJson()
    {
        return json_encode($this->result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 返回接口结果。
     *
     * @return void
     */
    public function getApiResult()
    {
        return $this->result;
    }

    /**
     * 从接口参数中获取一个整形数值。
     *
     * @param  string  $name          名称。
     * @param  int     $defaultValue  默认值。
     * @return int
     */
    public function getInt($name, $defaultValue = null)
    {
        return YInput::getInt($this->params, $name, $defaultValue);
    }

    /**
     * 从接口参数中获取一个字符串值。
     *
     * @param  string $name          名称。
     * @param  string $defaultValue  默认值。
     * @return string
     */
    public function getString($name, $defaultValue = null)
    {
        return YInput::getString($this->params, $name, $defaultValue);
    }

    /**
     * 从接口参数中获取一个浮点值。
     *
     * @param  string  $name           名称。
     * @param  float   $defaultValue   默认值。
     * @return float
     */
    public function getFloat($name, $defaultValue = null)
    {
        return YInput::getFloat($this->params, $name, $defaultValue);
    }

    /**
     * 从接口参数中获取一个浮点值。
     *
     * @param  string  $name          名称。
     * @param  array   $defaultValue  默认值。
     * @return array
     */
    public function getArray($name, $defaultValue = null)
    {
        return YInput::getArray($this->params, $name, $defaultValue);
    }

    /**
     * 可写接口是否允许访问。
     * 
     * @param  int  $userid  用户ID。等于0的情况则只要可写接口处于关闭状态就不允许访问。
     * 
     * @return \libs\Utils\ServiceException
     */
    protected function isAllowAccessApi($userid = 0)
    {
        $writeApiStatus   = YCore::appconfig('api.write_access');
        $writeApiCloseMsg = YCore::appconfig('api.write_close_msg');
        if (!$writeApiStatus) {
            if ($userid == 0) {
                YCore::exception(STATUS_SERVER_ERROR, $writeApiCloseMsg);
            } else {
                $whitelist = \explode(',', YCore::appconfig('api.write_userids'));
                $whitelist = array_unique($whitelist);
                if (!empty($whitelist)) {
                    if (!\in_array($userid, $whitelist)) {
                        YCore::exception(STATUS_SERVER_ERROR, $writeApiCloseMsg);
                    }
                } else {
                    YCore::exception(STATUS_SERVER_ERROR, $writeApiCloseMsg);
                }
            }
        }
    }

    /**
     * API 接口 IP 是否在白名单当中。
     * 
     * -- APP 调用的接口不受白名单限制。
     * 
     * @param  string  $appType  API 应用类型。
     * @param  string  $ip       IP 地址。
     * @return bool true-可访问、false-不可访问。
     */
    protected function isApiIPInWhiteList($appType, $ip)
    {
        $envName = YCore::appconfig('app.env');
        $envName = \strtolower($envName);
        if (!in_array($envName, ['beta', 'prod'])) { // 如果当前环境不是公测/正式。则不做 IP 限制。
            return true;
        }
        $appType = strtolower($appType);
        if ($appType == self::API_TYPE_APP) { // APP 调用的接口不受 IP 白名单限制。
            return true;
        }
        $whiteList = YCore::appconfig('app.inside_server_ip');
        if (empty($whiteList)) {
            return false;
        }
        if (in_array($ip, $whiteList)) {
            return true;
        } else {
            return false;
        }
    }
}