<?php
/**
 * 登录事件消费。
 * @author fingerQin
 * @date 2018-09-13
 */

namespace Services\Event\Sub;

class Login extends \Services\Event\Sub\AbstractBase
{
    /**
     * 运行真实的业务。
     * 
     * -- 当遇到严重错误，需要退出运行的时候直接抛出异常。
     * -- 如果仅仅是遇到诸如条件不满足某某规则则只记录日志即可。保证进程继续处理下一个。
     * 
     * @param array $event 事件数据。
     *
     * @return void
     */
    protected function runService($event)
    {
        
    }
}