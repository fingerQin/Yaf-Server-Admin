<?php
/**
 * 缓存操作。
 * @author fingerQin
 * @date 2018-06-28
 */

namespace Utils;

class YCache
{
    /**
     * 初始化缓存对象。
     *
     * @return \finger\cache\redis\Cache
     */
    private static function getInstace()
    {
        $ok = \Yaf_Registry::has('__system__cache__');
        if ($ok) {
            return \Yaf_Registry::get('__system__cache__');
        } else {
            $systemCache = new \finger\cache\redis\Cache();
            \Yaf_Registry::set('__system__cache__', $systemCache);
            return $systemCache;
        }
    }

    /**
     * 获取 Redis 对象。
     * 
     * -- 我们通常会用一些高级的操作虽然直接调用底层提供的方法。
     *
     * @return \Reids
     */
    public static function getRedisClient()
    {
        $cache = self::getInstace();
        return $cache->getClient();
    }

    /**
     * 缓存设置。
     *
     * @param  string        $Key    缓存键。
     * @param  string|array  $value  缓存值。
     * @param  int           $time   缓存时间。单位(秒)。
     * @return void
     */
    public static function set($key, $value, $time = 0)
    {
        $ret = (self::getInstace())->set($key, $value, $time);
        if ($ret != true) {
            YCore::exception(STATUS_ERROR, 'Redis set method call failed');
        }
    }

    /**
     * 读取缓存。
     *
     * @param  string  $key  缓存键。
     * @return string|array|bool
     */
    public static function get($key)
    {
        return (self::getInstace())->get($key);
    }

    /**
     * 删除缓存。
     *
     * @param  string  $key  缓存键。
     * @return void
     */
    public static function delete($key)
    {
        return (self::getInstace())->delete($key);
    }

    /**
     * 自增。
     *
     * @param  string  $key   缓存键。
     * @param  int     $step  自增步长。
     * @return int
     */
    public static function incr($key, $step = 1)
    {
        return (self::getInstace())->incr($key, $step);
    }

    /**
     * 自减。
     *
     * @param  string  $key   缓存键。
     * @param  int     $step  自增步长。
     * @return int
     */
    public static function decr($key, $step = 1)
    {
        return (self::getInstace())->decr($key, $step);
    }
}