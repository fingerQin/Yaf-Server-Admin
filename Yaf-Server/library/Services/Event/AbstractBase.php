<?php
/**
 * 事件基类。
 * @author fingerQin
 * @date 2018-09-13
 */

namespace Services\Event;

abstract class AbstractBase extends \Services\AbstractBase
{
    /**
     * 事件队列 KEY。
     */
    const EVENT_QUEUE_KEY = 'event-queue';

    /**
     * 子队列事件 KEY。
     */
    const EVENT_PREFIX = 'event-queue-sub';
}