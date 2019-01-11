<?php
/**
 * 生产者。
 * @author fingerQin
 * @date 2018-09-06
 */

namespace Services\Event;

use \Utils\YCore;
use \Utils\YCache;
use \Models\Event;
use finger\Validator;

class Producer extends \Services\Event\AbstractBase
{
    /**
     * 推送系统事件消息。
     *
     * @param  array  $mssage  事件消息。
     *
     * @return void
     */
    public static function push(array $message)
    {
        // [1]
        if (empty($message)) {
            YCore::exception(STATUS_SERVER_ERROR, '消息内容不能为空');
        }
        if (!isset($message['code'])) {
            YCore::exception(STATUS_SERVER_ERROR, '消息 CODE 必须设置');
        }
        $code = strtolower($message['code']);
        if (!in_array($code, Event::$codeDict)) {
            YCore::exception(STATUS_SERVER_ERROR, '事件 CODE 错误');
        }
        // [2]
        $code = ucfirst($code);
        self::{"check{$code}Event"}($message);
        // [3]
        $datetime = date('Y-m-d H:i:s', time());
        $data = [
            'code'   => $message['code'],
            'userid' => intval($message['userid']),
            'status' => Event::STATUS_INIT,
            'c_time' => $datetime,
            'u_time' => $datetime,
            'data'   => json_encode($message, JSON_UNESCAPED_UNICODE)
        ];
        $EventModel = new Event();
        $id = $EventModel->insert($data);
        if (!$id) {
            YCore::exception(STATUS_SERVER_ERROR, '系统繁忙,请稍候重试');
        }
        // [4] 写入 Redis 队列。
        $message['event_id']    = $id;
        $message['retry_count'] = 0; // 重试次数。用于队列重试时使用。
        $message['last_time']   = 0; // 最后重试的时间。0 代表还未重试
        $redis  = YCache::getRedisClient();
        $status = $redis->lPush(self::EVENT_QUEUE_KEY, json_encode($message, JSON_UNESCAPED_UNICODE));
        if ($status === false) {
            YCore::log($message, 'event', 'queue-error');
        }
    }

    /**
     * 注册事件验证。
     *
     * @param  array  $data  事件内容。
     * @return void
     *
     * -- eg:start --
     * $data = [
     *     'code'        => 'register',
     *     'userid'      => '用户ID',
     *     'mobile'      => '注册手机号',
     *     'platform'    => '平台标识',
     *     'app_v'       => 'APP 版本号,没有传空字符串',
     *     'v'           => 'API 版本号',
     *     'reg_time'    => '注册时间',
     *     'activity_id' => '活动ID',
     *     'invite_user' => '邀请人用户标识:openid'
     * ];
     * -- eg:end --
     */
    protected static function checkRegisterEvent($data)
    {
        $rules = [
            'code'        => 'CODE|require',
            'userid'      => '用户ID|require|integer|number_between:1:999999999',
            'mobile'      => '手机号|require|mobilephone',
            'platform'    => '平台标识|require|number_between:1:5',
            'v'           => 'API 版本号|require',
            'reg_time'    => '注册时间|require|datetime',
            'activity_id' => '活动ID|len:0:50:0'
        ];
        Validator::valido($data, $rules);
    }

    /**
     * 登录事件验证。
     *
     * @param  array  $data  事件内容。
     * @return void
     *
     * -- eg:start --
     * $data = [
     *     'code'        => 'register',
     *     'userid'      => '用户ID',
     *     'mobile'      => '注册手机号',
     *     'platform'    => '平台标识',
     *     'app_v'       => 'APP 版本号,没有传空字符串',
     *     'v'           => 'API 版本号',
     *     'login_time'  => '登录时间'
     * ];
     * -- eg:end --
     */
    protected static function checkLoginEvent($data)
    {
        $rules = [
            'code'       => 'CODE|require',
            'userid'     => '用户ID|require|integer|number_between:1:999999999',
            'mobile'     => '手机号|require|mobilephone',
            'platform'   => '平台标识|require|number_between:1:5',
            'v'          => 'API 版本号|require',
            'login_time' => '登录时间|require|datetime'
        ];
        Validator::valido($data, $rules);
    }
}