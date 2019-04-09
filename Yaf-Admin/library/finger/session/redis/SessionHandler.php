<?php
/**
 * 将SESSION封装到redis中保存。
 * @author fingerQin
 * @date 2016-09-11
 */

namespace finger\session\redis;

class SessionHandler implements \SessionHandlerInterface
{
    /**
     * Redis对象。
     *
     * @var Client
     */
    protected $_client;

    /**
     * session前缀。
     *
     * @var string
     */
    protected $_prefix = 'sess_';

    /**
     * session有效期。
     *
     * @var int
     */
    protected $_ttl;

    /**
     *
     * @var array
     */
    protected $_cache = [];

    /**
     * 构造方法。
     *
     * @param  Redis  $redis   Redis 连接对象。
     * @param  int    $ttl
     * @param  string $prefix
     * @return void
     */
    public function __construct(&$redis, $ttl = null, $prefix = 'sess_')
    {
        $this->_ttl    = $ttl ?  : ini_get('session.gc_maxlifetime');
        $this->_client = $redis;
        $this->_prefix = $prefix;
    }

    /**
     * 关闭当前session。
     *
     * @return bool
     */
    public function close()
    {
        $this->_client->close();
        return true;
    }

    /**
     * 删除 session
     * @param  string  $sessionId
     * @return bool
     */
    public function destroy($sessionId)
    {
        $this->_client->del($this->_prefix . $sessionId);
        return true;
    }

    /**
     * Redis 不需要 GC 清理过期的 session。Redis 会自己清掉。
     *
     * @param  int  $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     *
     * @param  string  $savePath
     * @param  string  $name
     * @return bool
     */
    public function open($savePath, $name)
    {
        return true;
    }

    /**
     * 读取session。
     *
     * @param  string  $sessionId
     * @return string
     */
    public function read($sessionId)
    {
        $realSessionId = $this->_prefix . $sessionId;
        if (isset($this->_cache[$realSessionId])) {
            return $this->_cache[$realSessionId];
        }
        $sessionData = $this->_client->get($realSessionId);
        return $this->_cache[$realSessionId] = ($sessionData === FALSE ? '' : $sessionData);
    }

    /**
     * 写session。
     *
     * @param  string  $sessionId
     * @param  string  $sessionData
     * @return bool
     */
    public function write($sessionId, $sessionData)
    {
        $realSessionId = $this->_prefix . $sessionId;
        if (isset($this->_cache[$realSessionId]) && $this->_cache[$realSessionId] === $sessionData) {
            $this->_client->expire($realSessionId, $this->_ttl);
        } else {
            $this->_cache[$realSessionId] = $sessionData;
            $this->_client->setEx($realSessionId, $this->_ttl, $sessionData);
        }
        return true;
    }
}