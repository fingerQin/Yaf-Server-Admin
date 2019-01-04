<?php
/**
 * DB 连接封装。
 * @author fingerQin
 * @date 2018-07-13
 */

namespace finger\Database;

use Utils\YCore;
use Utils\YLog;

class Connection
{
    /**
     * 数据库连接资源句柄。
     *
     * @var \PDO
     */
    protected $dbConnection = null;

    /**
     * 在使用预处理语句时使用。
     * -- 即建立一个只能向后的指针。
     *
     * @var array
     */
    protected $prepareAttr = [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY];

    /**
     * 连接哪个数据库配置。对应系统配置文件 config.ini 当中 mysql.xxx.host 的 xxx
     *
     * @var string
     */
    protected $dbOption = 'default';

    /**
     *
     * @var 保存最后操作的 \PDOStatement 对象。
     */
    protected $stmt = null;

    /**
     * 当前运行的 SQL 记录。
     *
     * @var array
     */
    protected $runSqlRecords = [];

    /**
     * 构造方法。
     *
     * @param  string  $dbOption  数据库配置项。
     * @return void
     */
    public function __construct($dbOption = '')
    {
        if (strlen($dbOption) > 0) {
            $this->dbOption = $dbOption;
        }
        $this->changeDb($this->dbOption);
    }

    /**
     * 切换数据库连接。
     *
     * @param  string  $dbOption  数据库配置项。
     * @return void
     */
    final public function changeDb($dbOption)
    {
        $registryName = "mysql_{$dbOption}";
        if (\Yaf_Registry::has($registryName) === false) {
            $this->connection($dbOption);
        }
        $this->dbConnection = \Yaf_Registry::get($registryName);
    }

    /**
     * 返回真实的数据库对象。
     * @return PDO
     */
    final public function getDbClient()
    {
        return $this->dbConnection;
    }

    /**
     * 连接数据库。
     *
     * @param  string  $dbOption  数据库配置项。
     * @return void
     */
    final public function connection($dbOption = '')
    {
        if (strlen($dbOption) > 0) {
            $this->dbOption = $dbOption;
        }
        $registryName  = "mysql_{$this->dbOption}";
        // [1] 传统初始化MySQL方式。
        $config   = YCore::appconfig("mysql.{$this->dbOption}");
        $host     = $config['host'];
        $port     = $config['port'];
        $username = $config['user'];
        $password = $config['pwd'];
        $charset  = $config['charset'];
        $dbname   = $config['dbname'];
        $pconnect = $config['pconnect'];
        $dsn      = "mysql:dbname={$dbname};host={$host};port={$port}";
        $dbh      = new \PDO($dsn, $username, $password, [\PDO::ATTR_PERSISTENT => $pconnect]);
        // MySQL操作出错，抛出异常。
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $dbh->setAttribute(\PDO::ATTR_ORACLE_NULLS, \PDO::NULL_NATURAL);
        $dbh->setAttribute(\PDO::ATTR_STRINGIFY_FETCHES, FALSE);
        $dbh->setAttribute(\PDO::ATTR_EMULATE_PREPARES, FALSE);
        // 以关联数组返回查询结果。
        $dbh->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $dbh->query("SET NAMES {$charset}");
        \Yaf_Registry::set($registryName, $dbh);
    }

    /**
     * 关闭数据库连接。
     *
     * @param  string  $dbOption  数据库选项标识。空字符串关闭所有链接。
     *
     * @return void
     */
    final public static function close($dbOption = '')
    {
        if (strlen($dbOption) == 0) {
            $dbOption = $this->dbOption;
        }
        // [1] 取配置选项。
        $dbOptions = [];
        if (strlen($dbOption) > 0) {
            $dbOptions[] = $dbOption;
        } else {
            $mysqlConfigs = YCore::appconfig('mysql');
            foreach($mysqlConfigs as $dbOption => $config) {
                $dbOptions[] = $dbOption;
            }
        }
        // [2] 根据选项关闭数据库连接。
        foreach($dbOptions as $dbOption) {
            $registryName = "mysql_{$dbOption}";
            if (\Yaf_Registry::has($registryName) === true) {
                $dbh = \Yaf_Registry::get($registryName);
                $dbh = null;
                \Yaf_Registry::set($registryName, null);
            }
        }
    }

    /**
     * 数据库重连。
     *
     * @param  string  $dbOption  数据库配置项。断线重连时，以哪个数据库配置重连。
     * 
     * @return void
     */
    final public function reconnect($dbOption = '')
    {
        if (strlen($dbOption) == 0) {
            $dbOption = $this->dbOption;
        }
        $registryName = "mysql_{$dbOption}";
        $this->connection($dbOption);
        $this->dbConnection = \Yaf_Registry::get($registryName);
    }

    /**
     * 检查连接是否可用(类似于http ping)。
     * 
     * -- 向 MySQL 服务器发送获取服务器信息的请求。
     * 
     * @param  int     $isReconnect  当与 MySQL 服务器的连接不可用时,是否重连。默认断线重连。
     * @param  string  $dbOption     数据库配置项。断线重连时，以哪个数据库配置重连。
     * 
     * @return bool
     */
    final public function ping($isReconnect = true, $dbOption = '')
    {
        if (strlen($dbOption) == 0) {
            $dbOption = $this->dbOption;
        }
        if (!$this->dbConnection) {
            YCore::exception(-1, '请正确连接数据库');
        }
        try {
            $info = $this->dbConnection->getAttribute(\PDO::ATTR_SERVER_INFO);
            if (is_null($info)) {
                if ($isReconnect) {
                    $this->reconnect($dbOption);
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } catch (\Exception $e) {
            if ($isReconnect) {
                $this->reconnect($dbOption);
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 开启数据库事务。
     */
    final public function beginTransaction()
    {
        $isActive = $this->dbConnection->inTransaction();
        if (!$isActive) {
            $bool = $this->dbConnection->beginTransaction();
            if (!$bool) {
                $this->openTransactionFailed();
            }
        }
    }

    /**
     * 提交数据库事务。
     */
    final public function commit()
    {
        $isActive = $this->dbConnection->inTransaction();
        if ($isActive) {
            $bool = $this->dbConnection->commit();
            if (!$bool) {
                $this->commitTransactionFailed();
            }
        }
    }

    /**
     * 回滚数据库事务。
     */
    final public function rollBack()
    {
        $isActive = $this->dbConnection->inTransaction();
        if ($isActive) {
            $bool = $this->dbConnection->rollBack();
            if (!$bool) {
                $this->rollbackTransactionFailed();
            }
        }
    }

    /**
     * 事务开启失败。
     * @return void
     */
    protected function openTransactionFailed()
    {
        YCore::exception(-1, 'Open transaction failure');
    }

    /**
     * 提交事务失败。
     * @return void
     */
    protected function commitTransactionFailed()
    {
        YCore::exception(-1, 'Transaction commit failure');
    }

    /**
     * 提交事务失败。
     * @return void
     */
    protected function rollbackTransactionFailed()
    {
        YCore::exception(-1, 'Transaction rollback failed');
    }

    /**
     * 记录 SQL 日志。
     * 
     * -- 正式环境不记录执行的 SQL
     *
     * @param  string  $sql     执行的 SQL。
     * @param  array   $params  SQL 参数。
     * @return void
     */
    final public function writeSqlLog($sql, $params = [])
    {
        $env = YCore::appconfig('app.env');
        if ($env != 'prod') {
            foreach ($params as $key => $val) {
                $val = "'" . addslashes($val) . "'";
                $sql = str_replace($key, $val, $sql);
            }
            $this->pushRunSqlRecords($sql);
        }
    }

    /**
     * 返回当前已执行的 SQL 记录。
     *
     * @return array
     */
    final public function getRunSqlRecords()
    {
        return $this->runSqlRecords;
    }

    /**
     * 记录执行的 SQL。
     *
     * @param  string  $sql  SQL 语句。
     * @return void
     */
    final public function pushRunSqlRecords($sql)
    {
        array_push($this->runSqlRecords, $sql);
    }

    /**
     * 析构方法。
     * -- 处理日志。
     */
    public function __destruct()
    {
        if (YCore::appconfig('app.debug')) {
            YLog::log($this->runSqlRecords, 'sql', 'log', YLog::LOG_TYPE_NONE);
        }
    }
}