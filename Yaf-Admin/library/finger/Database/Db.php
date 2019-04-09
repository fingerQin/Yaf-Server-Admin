<?php
/**
 * Db 操作。
 * @author fingerQin
 * @date 2018-07-13
 */

namespace finger\Database;

class Db
{
    /**
     * 当前对象实例。
     *
     * @var \finger\Database\Db
     */
    protected static $_instance = null;

    /**
     * 数据库连接。
     *
     * @var [type]
     */
    protected $dbConnection = null;

    /**
     * PDO 连接。
     *
     * @var [type]
     */
    protected $PDO = null;

    /**
     * 构造方法。
     */
    private function __construct($dbOption = 'default')
    {
        $this->dbConnection = new \finger\Database\Connection($dbOption);
        $this->PDO = $this->dbConnection->getDbClient();
    }

    /**
     * 获取当前对象实例。
     * 
     * @param  string  $dbOption  数据库选项参数。
     * 
     * @return finger\Database\Db
     */
    public static function getInstance($dbOption = 'default')
    {
        if(!(self::$_instance instanceof self)) {    
            self::$_instance = new self($dbOption);
        } else {
            self::$_instance->changeDb($dbOption);
        }
        return self::$_instance;
    }

    /**
     * 防止克隆导致单例失败。
     * 
     * @return void
     */
    private function __clone() {}

    /**
     * 切换数据库
     *
     * @param  string  $dbOption  数据库选项参数。
     * @return void
     */
    public function changeDb($dbOption)
    {
        $this->dbConnection->changeDb($dbOption);
        $this->PDO = $this->dbConnection->getDbClient();
    }

    public function getPDO()
    {
        return $this->PDO;
    }

    public function getDbConnection()
    {
        return $this->dbConnection;
    }

    /**
     * 开启数据库事务。
     */
    final public static function beginTransaction()
    {
        $instace = self::getInstance();
        ($instace->getDbConnection())->beginTransaction();
    }

    /**
     * 提交数据库事务。
     */
    final public static function commit()
    {
        $instace = self::getInstance();
        ($instace->getDbConnection())->commit();
    }

    /**
     * 回滚数据库事务。
     */
    final public static function rollBack()
    {
        $instace = self::getInstance();
        ($instace->getDbConnection())->rollBack();
    }

    /**
     * 数据更新/写入。
     *
     * @param  string  $sql       待执行的 SQL。
     * @param  array   $params    参数。
     * @param  bool    $isMaster  是否主库操作。
     * 
     * @return int 返回受影响的记录条数/插入时成功则返回主键ID值。
     */
    public static function execute($sql, $params = [], $dbOption = 'default')
    {
        $opSymbol     = substr(ltrim($sql, ' '), 0, 6);
        $_instance    = self::getInstance($dbOption);
        $PDO          = $_instance->getPDO();
        $dbConnection = $_instance->getDbConnection();
        $dbConnection->writeSqlLog($sql, $params);
        $sth = $PDO->prepare($sql);
        $ok  = $sth->execute($params);
        if ($ok) {
            if (strtolower($opSymbol) == 'insert') {
                return $PDO->lastInsertId();
            } else {
                return $sth->rowCount();
            }
        } else {
            return 0;
        }
    }

    /**
     * 返回所有记录。
     * 
     * @param  string  $sql       SQL 查询语句。
     * @param  array   $params    条件。
     * @param  bool    $isMaster  是否主库操作。
     * @param  string  $dbOption  数据库选项标识。
     * 
     * @return array 以数据返回查询结果。
     */
    public static function query($sql, $params = [], $isMaster = false, $dbOption = 'default')
    {
        if ($isMaster) {
            $sql = '/*FORCE_MASTER*/ ' . $sql;
        }
        $_instance    = self::getInstance($dbOption);
        $PDO          = $_instance->getPDO();
        $dbConnection = $_instance->getDbConnection();
        $dbConnection->writeSqlLog($sql, $params);
        $sth = $PDO->prepare($sql);
        $sth->execute($params);
        $data = $sth->fetchAll(\PDO::FETCH_ASSOC);
        return $data ? $data : [];
    }

    /**
     * 返回所有记录。
     * 
     * @param  string  $sql       SQL 查询语句。
     * @param  array   $params    条件。
     * @param  bool    $isMaster  是否主库操作。
     * @param  string  $dbOption  数据库选项标识。
     * 
     * @return array 以二维数组返回查询结果。
     */
    public static function all($sql, $params = [], $isMaster = false, $dbOption = 'default')
    {
        return self::query($sql, $params, $isMaster, $dbOption);
    }

    /**
     * 返回一条记录。
     * 
     * @param  string  $sql       SQL 查询语句。
     * @param  array   $params    条件。
     * @param  bool    $isMaster  是否主库操作。
     * @param  string  $dbOption  数据库选项标识。
     * 
     * @return array 返回单条记录的数组结果。
     */
    public static function one($sql, $params = [], $isMaster = false, $dbOption = 'default')
    {
        if ($isMaster) {
            $sql = '/*FORCE_MASTER*/ ' . $sql;
        }
        $_instance    = self::getInstance($dbOption);
        $dbConnection = $_instance->getDbConnection();
        $PDO          = $_instance->getPDO();
        $dbConnection->writeSqlLog($sql, $params);
        $sth = $PDO->prepare($sql);
        $sth->execute($params);
        $data = $sth->fetch(\PDO::FETCH_ASSOC);
        return $data ? $data : [];
    }

    /**
     * 统计记录条数。
     * 
     * @param  string  $sql       SQL 查询语句。
     * @param  array   $params    条件。
     * @param  bool    $isMaster  是否主库操作。
     * @param  string  $dbOption  数据库选项标识。
     * 
     * @return int 有记录大于0，无记录等于0。
     */
    public static function count($sql, $params = [], $isMaster = false, $dbOption = 'default')
    {
        if ($isMaster) {
            $sql = '/*FORCE_MASTER*/ ' . $sql;
        }
        $_instance    = self::getInstance($dbOption);
        $PDO          = $_instance->getPDO();
        $dbConnection = $_instance->getDbConnection();
        $dbConnection->writeSqlLog($sql, $params);
        $sth = $PDO->prepare($sql);
        $sth->execute($params);
        $data = $sth->fetch(\PDO::FETCH_ASSOC);
        return $data ? intval($data['count']) : 0;
    }
}