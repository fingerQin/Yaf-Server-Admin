<?php
/**
 * 所有 API 接口基类。
 * 
 * @author fingerQin
 * @date 2018-06-27
 */

namespace Apis;

use finger\App;
use finger\Core;
use finger\DataInput;
use finger\Ip;
use finger\Validator;
use Services\AccessForbid\Forbid;

abstract class AbstractApi
{
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
    public function __construct(&$data, $apiType, $apiKey = '', $apiSecret = '')
    {
        $this->apiType   = $apiType;
        $this->timestamp = $_SERVER['REQUEST_TIME'];
        $this->params    = $data;
        $this->apiKey    = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->checkIpAccessPermission();
        $this->checkTimeLag();
        $this->checksignature();
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
     * 验证当前访问接口的 IP 是否有权限。
     *
     * -- 1、只有 app 类型接口才做此限制。因为，其他类型的接口有限制指定 IP 才可访问。
     * -- 2、只对一些对系统性能、资源消耗（短信）、信息安全（用户数据撞库泄漏）等接口做限制。
     * 
     * @return void
     */
    protected function checkIpAccessPermission()
    {
        $apiMethod = $this->params['method'];
        if ($this->apiType == 'app' && in_array($apiMethod, API_MUST_FORBID_IP_LIST)) {
            $ip = Ip::ip();
            Forbid::check($ip);
        }
    }

    /**
     * 验证时差。
     *
     * @return void
     */
    protected function checkTimeLag()
    {
        $reqTsp = $this->params['timestamp'];
        if (!Validator::is_integer($reqTsp)) {
            Core::exception(STATUS_SERVER_ERROR, 'timestamp 参数格式不正确');
        }
        if (strlen($reqTsp) != 10) {
            Core::exception(STATUS_SERVER_ERROR, 'timestamp 参数必须为 10 位长度的秒值');
        }
        $diffSecond = $this->timestamp - $reqTsp;
        if ($diffSecond > 600) {
            Core::exception(STATUS_SERVER_ERROR, 'timestamp 已经超时请求');
        }
    }

    /**
     * 验证请求签名。
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
     * @param  string  $appType  当前调用 APPID 对应的应用类型。 
     *
     * @return void
     */
    protected function checkApiTypeAuth($appType)
    {
        if (\strtolower($appType) != $this->apiType) {
            Core::exception(STATUS_SERVER_ERROR, '您没有调用该接口的权限');
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
            Core::exception(STATUS_ERROR, 'BaseApi render method of code parameter must be an integer');
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
     * 返回结果。
     *
     * @return array
     */
    public function getResult()
    {
        return $this->result;
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
        return DataInput::getInt($this->params, $name, $defaultValue);
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
        return DataInput::getString($this->params, $name, $defaultValue);
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
        return DataInput::getFloat($this->params, $name, $defaultValue);
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
        return DataInput::getArray($this->params, $name, $defaultValue);
    }

    /**
     * 可写接口是否允许访问。
     * 
     * @param  int  $userid  用户ID。等于0的情况则只要可写接口处于关闭状态就不允许访问。
     * 
     * @return \finger\Exception\ServiceException
     */
    protected function isAllowAccessApi($userid = 0)
    {
        $writeApiStatus   = App::getConfig('api.write_access');
        $writeApiCloseMsg = App::getConfig('api.write_close_msg');
        if (!$writeApiStatus) {
            if ($userid == 0) {
                Core::exception(STATUS_SERVER_ERROR, $writeApiCloseMsg);
            } else {
                $whitelist = \explode(',', App::getConfig('api.write_userids'));
                $whitelist = array_unique($whitelist);
                if (!empty($whitelist)) {
                    if (!\in_array($userid, $whitelist)) {
                        Core::exception(STATUS_SERVER_ERROR, $writeApiCloseMsg);
                    }
                } else {
                    Core::exception(STATUS_SERVER_ERROR, $writeApiCloseMsg);
                }
            }
        }
    }
}