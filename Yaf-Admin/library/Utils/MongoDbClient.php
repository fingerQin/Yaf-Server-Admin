<?php
/**
 * MongoDB 简单封装。
 * @author fingerQin
 * @date 2018-07-04
 */

namespace Utils;

class MongoDbClient
{
    /**
     * MongoDB 连接对象。
     * 
     * @var \MongoDB\Driver\Manager
     */
    private $manager = '';

    private static $instance;

    /**
     * 数据类型。
     */
    const LOG_TYPE_SERVICE_ERROR = 'service_error';         // 业务错误日志。
    const LOG_TYPE_SYSTEM_ERROR  = 'error';                 // 系统错误日志。
    const LOG_TYPE_SERVICE_LOG   = 'service_log';           // 业务操作日志。

    private function __construct()
    {
        $user = config('mongodb.default.user');
        $port = config('mongodb.default.port');
        $host = config('mongodb.default.host');
        $pass = config('mongodb.default.pass');
        $this->manager = new \MongoDB\Driver\Manager("mongodb://{$user}:{$pass}@{$host}:{$port}");
    }

    /**
     * 获取 kafka 对象实例。
     * 
     * @return void
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 向 MongoDB 写入数据。
     * 
     * @param  string|array $data 日志内容。
     * @param  int          $type 类型。1-API请求日志、2-业务日志、3-错误日志。
     * 
     * @return void
     */
    public function write($data, $type = self::DATA_TYPE_API_REQUEST)
    {
        $data['log_type_mongo'] = $type;
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->insert($data);
        $collectionName = config('mongodb.collection');
        $this->manager->executeBulkWrite("{$collectionName}.{$type}", $bulk);
    }

    private function __clone()
    {
        YCore::exception(STATUS_ERROR, '不能克隆对象');
    }
}