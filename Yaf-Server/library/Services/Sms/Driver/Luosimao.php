<?php
/**
 * luosimao.com 短信发送封装。
 * @author fingerQin
 * @date 2018-09-11
 */

namespace Services\Sms\Driver;

use finger\Utils\YLog;
use finger\Utils\YCore;

class Luosimao
{
    /**
     * 短信发送密钥。
     *
     * @var string
     */
    public $key = '';

    /**
     * 结构方法。
     * 
     * -- 初始化配置。
     *
     * @param array $config  短信配置。
     * @return void
     */
    public function __construct($config)
    {
        if (!isset($config['channel_secret'])) {
            YCore::exception(STATUS_SERVER_ERROR, '短信配置不正确');
        }
        $this->key = $config['channel_secret'];
    }

    /**
     * 发送短信。
     * 
     * @param  string  $mobile   手机号码。
     * @param  string  $message  短信消息。
     *
     * @return void
     */
    public function send($mobile, $message)
    {
        if (strlen($mobile) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '请设置接收短信的手机号');
        }
        if (strlen($message) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '请设置短信内容');
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://sms-api.luosimao.com/v1/send.json');
        curl_setopt($ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD , "api:key-{$this->key}");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['mobile' => $mobile, 'message' => $message]);
        $response = curl_exec($ch);
        $errMsg   = curl_error($ch);
        $errCode  = curl_errno($ch);
        if ($errCode > 0) {
            YLog::log(['errMsg' => $errMsg, 'errCode' => $errCode], 'curl', 'sms-lousimao-send');
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        curl_close($ch);
        if ($response === FALSE) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        YLog::log(['mobile' => $mobile, 'message' => $message, 'response' => $response], 'sms', 'lousimao');
        $result = json_decode($response, TRUE);
        if (!is_array($result)) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        if ($result['error'] != 0) {
            YCore::exception(STATUS_SERVER_ERROR, "{$result['error']}:{$result['msg']}");
        }
    }

    /**
     * 批量发送短信(通知/提醒)。
     * 
     * @param  array   $mobile   手机号码组。格式：[14800000000,14800000001]
     * @param  string  $message  短信消息。 如：您的 VIP 资格即将到期，请及时续费【xx测试】
     * @param  string  $time     定时发送时间。格式：2018-09-09 12:00:00。只能为当日的时间。为空字符串则立即发送。
     * 
     * @return void
     */
    public function batchSend(array $mobile, $message, $time = '')
    {
        if (empty($mobile)) {
            YCore::exception(STATUS_SERVER_ERROR, '请设置接收短信的手机号');
        }
        if (strlen($message) === 0) {
            YCore::exception(STATUS_SERVER_ERROR, '请设置短信内容');
        }
        $postData = ['mobile' => implode(',', $mobile), 'message' => $message];
        if (strlen($time) > 0) {
            $postData['time'] = $time;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://sms-api.luosimao.com/v1/send_batch.json');
        curl_setopt($ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD , "api:key-{$this->key}");
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $response = curl_exec($ch);
        $errMsg   = curl_error($ch);
        $errCode  = curl_errno($ch);
        if ($errCode > 0) {
            YLog::log(['errMsg' => $errMsg, 'errCode' => $errCode], 'curl', 'sms-lousimao-batch');
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        curl_close($ch);
        if ($response === FALSE) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        $result = json_decode($response, TRUE);
        if (!is_array($result)) {
            YCore::exception(STATUS_ERROR, '服务器繁忙,请稍候重试');
        }
        if ($result['error'] != 0) {
            YCore::exception(STATUS_SERVER_ERROR, $result['msg']);
        }
    }
}