<?php
/**
 * 系统已有 Redis 数据管理。
 * @author fingerQin
 * @date 2019-06-03
 */

namespace Services\System;

use finger\Utils\YCache;

class RedisManage extends \Services\AbstractBase
{
    /**
     * 待管理的 Redis 缓存 KEY。
     *
     * @var array
     */
    public static $redisKeys = [
        'event-queue'                => 'event-queue: 事件总队列 KEY。',
        'event-quque-sub'            => 'event-quque-sub_*: 子事件队列 KEY。* 符号替换为对应的子事件 CODE 编码。',
        'sms-squeue'                 => 'sms-squeue: 短信队列 KEY。',
        'sms-blacklist-mobile'       => 'sms-blacklist-mobile: 短信号码黑名单 KEY。',
        'u_t_k:%loginType%:%userid%' => 'u_t_k:%loginType%:%userid% : 用户会话 TOKEN 令牌。loginType 登录类型：1 代表 App, 0 - 非 App。userid 代表用户ID。'
    ];

    /**
     * 缓存删除。
     *
     * @param string $keys 缓存 KEY。
     *
     * @return void
     */
    public static function delete($keys)
    {
        $redis = YCache::getRedisClient();
        $redis->del($keys);
    }
}