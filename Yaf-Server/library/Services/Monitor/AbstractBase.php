<?php
/**
 * 监控基类。
 * @author fingerQin
 * @date 2019-06-12
 */

namespace Services\Monitor;

abstract class AbstractBase extends \Services\AbstractBase
{
    /**
     * 监控队列 KEY。
     */
    const MONITOR_QUEUE_KEY = 'monitor-queue';
}