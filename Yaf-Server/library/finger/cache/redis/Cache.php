<?php
/**
 * Redis缓存。
 * 
 * @author fingerQin
 * @date 2016-09-11
 */

namespace finger\cache\redis;

use Utils\YCore;
use finger\Validator;

class Cache
{
    /**
     * 当前对象。
     * @var finger\cache\redis
     */
    protected $client = null;

    public function __construct()
    {
        $clientName = 'winer_cache_redis';
        if (\Yaf_Registry::has($clientName)) {
            $this->client = \Yaf_Registry::get($clientName);
            $redisIndex   = YCore::appconfig('redis.default.index');
            $this->client->select($redisIndex); // 必须显示切换到指定的 Redis 库。避免使用过程中被其他程序切换未还原。
        } else {
            $this->client = $this->connect();
            \Yaf_Registry::set($clientName, $this->client);
        }
    }

    /**
     * 获取 Redis 客户端连接。
     *
     * @return finger\cache\redis
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * 连接 redis
     */
    protected function connect()
    {
        $ok = \Yaf_Registry::has('redis');
        if ($ok) {
            return \Yaf_Registry::get('redis');
        } else {
            $config     = YCore::appconfig('redis.default');
            $redisHost  = $config['host'];
            $redisPort  = $config['port'];
            $redisAuth  = $config['auth'];
            $redisIndex = $config['index'];
            $redis = new \Redis();
            $redis->connect($redisHost, $redisPort);
            $redis->auth($redisAuth);
            $redis->select($redisIndex);
            \Yaf_Registry::set('redis', $redis);
            return $redis;
        }
    }

    /**
     * 自增。
     * @param  string  $cacheKey  缓存 KEY。
     * @param  int     $step      自增步长。
     * @return int 自增之后的值。
     */
    public function incr($cacheKey, $step = 1)
    {
        if (!Validator::is_integer($step) || $step <= 0) {
            YCore::exception(STATUS_ERROR, 'Redis incr step error');
        }
        return $this->client->incr($cacheKey, $step);
    }

    /**
     * 自减。
     * @param  string  $cacheKey  缓存 KEY。
     * @param  int     $step      自增步长。
     * @return int 自增之后的值。
     */
    public function decr($cacheKey, $step = 1)
    {
        if (!Validator::is_integer($step) || $step <= 0) {
            YCore::exception(STATUS_ERROR, 'Redis decr step error');
        }
        return $this->client->decr($cacheKey, $step);
    }

    /**
     * 获取缓存。
     * @param  string  $cacheKey  缓存 KEY。
     * @return string|array|bool
     */
    public function get($cacheKey)
    {
        $cacheData = $this->client->get($cacheKey);
        return $cacheData ? json_decode($cacheData, true) : false;
    }

    /**
     * 写缓存。
     * @param  string        $cacheKey   缓存 KEY。
     * @param  string|array  $cacheData  缓存数据。
     * @param  integer       $expire     生存时间。单位(秒)。0 代表永久生效。
     * @return bool
     */
    public function set($cacheKey, $cacheData, $expire = 0)
    {
        if ($expire > 0) {
            return $this->client->setEx($cacheKey, $expire, json_encode($cacheData));
        } else {
            return $this->client->set($cacheKey, json_encode($cacheData));
        }
    }

    /**
     * 删除缓存。
     * @param  string  $cacheKey
     * @return bool
     */
    public function delete($cacheKey)
    {
        return $this->client->del($cacheKey);
    }
}