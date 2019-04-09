<?php
/**
 * MySQL缓存。
 * -- 将缓存存储到MySQL。
 * 
 * @author fingerQin
 * @date 2016-09-11
 */

namespace finger\cache\mysql;

use Utils\YCore;
use finger\Database\Connection;

class Cache
{
    /**
     * 当前对象。
     * @var finger\cache\mysql
     */
    protected $client = null;

    public function __construct()
    {
        $clientName = 'finger_cache_mysql';
        if (\Yaf_Registry::has($clientName)) {
            $this->client = \Yaf_Registry::get($clientName);
        } else {
            $this->client = $this->connect();
            \Yaf_Registry::set($clientName, $this->client);
        }
    }

    /**
     * 连接数据库。
     */
    protected function connect()
    {
        return new Connection('default');
    }

    /**
     * 获取缓存。
     * @param  string  $cacheKey  缓存KEY。
     * @return string|array|boolean
     */
    public function get($cacheKey)
    {
        $dbLink = $this->client->getDbClient();
        $sql    = 'SELECT * FROM ms_cache WHERE cache_key = :cache_key';
        $sth    = $dbLink->prepare($sql);
        $sth->bindParam(':cache_key', $cacheKey, \PDO::PARAM_STR);
        $sth->execute();
        $cacheData = $sth->fetch();
        if ($cacheData !== FALSE && (($cacheData['cache_expire'] == 0) || 
            ($cacheData['cache_expire'] > time()))) {
            return json_decode($cacheData['cache_data'], true);
        } else {
            return false;
        }
    }

    /**
     * 自增1。
     * @param  string  $cacheKey  缓存 KEY。
     * @return int 自增之后的值。
     */
    public function incr($cacheKey)
    {
        YCore::exception(STATUS_ERROR, 'The MySQL Cache plugin does not implement incr()');
    }

    /**
     * 写缓存。
     * @param  string        $cacheKey   缓存KEY。
     * @param  string|array  $cacheData  缓存数据。
     * @param  int           $cacheTime  缓存时间。0代表永久生效。
     * @return bool
     */
    public function set($cacheKey, $cacheData, $cacheTime = 0)
    {
        $sql           = 'REPLACE INTO ms_cache (cache_key, cache_expire, cache_data) '
                       . 'VALUES(:cache_key, :cache_expire, :cache_data)';
        $cacheTime     = $cacheTime <= 0 ? 0 : time() + $cacheTime;
        $cacheDataJson = json_encode($cacheData);
        $dbLink        = $this->client->getDbClient();
        $sth           = $dbLink->prepare($sql);
        $sth->bindParam(':cache_key', $cacheKey, \PDO::PARAM_STR);
        $sth->bindParam(':cache_expire', $cacheTime, \PDO::PARAM_INT);
        $sth->bindParam(':cache_data', $cacheDataJson, \PDO::PARAM_STR);
        return $sth->execute();
    }

    /**
     * 删除缓存。
     * @param  string  $cache_key
     * @return bool
     */
    public function delete($cacheKey)
    {
        $sql    = 'DELETE FROM ms_cache WHERE cache_key = :cache_key';
        $dbLink = $this->client->getDbClient();
        $sth    = $dbLink->prepare($sql);
        $sth->bindParam(':cache_key', $cacheKey, \PDO::PARAM_STR);
        $sth->execute();
        return true;
    }
}