<?php
/**
 * 将SESSION封装到MySQL中保存。
 * --------------------------
 * CREATE TABLE ms_session (
 *   session_id varchar(100) NOT NULL COMMENT 'php session_id',
 *   session_expire int(11) UNSIGNED NOT NULL COMMENT 'session到期时间',
 *   session_data blob,
 *   UNIQUE KEY `session_id` (`session_id`)
 * )ENGINE = MyISAM DEFAULT CHARSET=utf8 COMMENT 'session表';
 * --------------------------
 * -- 错误码：3005xxx
 * @author fingerQin
 * @date 2016-09-11
 */

namespace finger\session\mysql;

class SessionHandler implements \SessionHandlerInterface {

    /**
     * mysql对象。
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
     * @param PDO    $pdo MySQL连接对象。
     * @param int    $ttl
     * @param string $prefix
     * @throws \Exception
     */
    public function __construct(&$pdo, $ttl = null, $prefix = 'sess_') {
        $this->_ttl    = $ttl ?  : ini_get('session.gc_maxlifetime');
        $this->_client = $pdo;
        $this->_prefix = $prefix;
    }

    /**
     * 关闭当前session。
     *
     * @return bool
     */
    public function close() {
        return true;
    }

    /**
     * 清除session。
     * @param string $session_id
     * @return bool
     */
    public function destroy($session_id) {
        $sql = 'DELETE FROM ms_session WHERE session_id = :session_id';
        $session_id = $this->_prefix . $session_id;
        $sth = $this->_client->prepare($sql);
        $sth->bindParam(':session_id', $session_id, \PDO::PARAM_STR);
        $sth->execute();
        return true;
    }

    /**
     * session 垃圾回收。
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime) {
        $sql = 'DELETE FROM ms_session WHERE session_expire < :session_expire';
        $sth = $this->_client->prepare($sql);
        $sth->bindParam(':session_expire', $maxlifetime, \PDO::PARAM_INT);
        try {
            $sth->execute();
        } catch (\Exception $e) {
            YCore::exception(-1, "\finger\session\mysql\SessionHandler::gc method is wrong");
        }
        return true;
    }

    /**
     *
     * @param string $save_path
     * @param string $name
     * @return boolean
     */
    public function open($save_path, $name) {
        return true;
    }

    /**
     * 读取session。
     *
     * @param string $session_id
     * @return string
     */
    public function read($session_id) {
        $real_session_id = $this->_prefix . $session_id;
        if (isset($this->_cache[$real_session_id])) {
            return $this->_cache[$real_session_id];
        }
        $sql = 'SELECT * FROM ms_session WHERE session_id = :session_id';
        $sth = $this->_client->prepare($sql);
        $sth->bindParam(':session_id', $real_session_id, \PDO::PARAM_STR);
        try {
            $sth->execute();
            $result = $sth->fetch(\PDO::FETCH_ASSOC);
            if ($result) {
                if ($result['session_expire'] < time()) {
                    $this->destroy($session_id);
                    return ''; // session已经过期。
                }
                $sess_data = json_decode($result['session_data'], true);
                $sess_data = $sess_data === null ? '' : $sess_data;
                $this->_cache[$real_session_id] = $sess_data;
                return $sess_data;
            } else {
                return '';
            }
        } catch (\Exception $e) {
            throw new \Exception('服务器繁忙', -1);
        }
    }

    /**
     * 写session。
     *
     * @param string $session_id
     * @param string $session_data
     * @return bool
     */
    public function write($session_id, $session_data) {
        $real_session_id = $this->_prefix . $session_id;
        $this->_cache[$real_session_id] = $session_data;
        $session_data_json = json_encode($session_data);
        $ttl = $this->_ttl + time();
        $sql = 'REPLACE INTO ms_session(session_id, session_expire, session_data) VALUES(:session_id, :session_expire, :session_data)';
        $sth = $this->_client->prepare($sql);
        $sth->bindParam(':session_id', $real_session_id, \PDO::PARAM_STR);
        $sth->bindParam(':session_expire', $ttl, \PDO::PARAM_INT);
        $sth->bindParam(':session_data', $session_data_json, \PDO::PARAM_STR);
        $sth->execute();
        return true;
    }

}