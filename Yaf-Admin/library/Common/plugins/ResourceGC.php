<?php
/**
 * 资源回收。
 * @author fingerQin
 * @date 2018-11-19
 */

namespace Common\plugins;

use finger\Database\Connection;


class ResourceGC extends \Yaf_Plugin_Abstract
{
    /**
     * 资源回收动作。
     *
     * @param Yaf_Request_Abstract $request
     * @param Yaf_Response_Abstract $response
     *
     * @return void
     */
    public function dispatchLoopShutdown(\Yaf_Request_Abstract $request, \Yaf_Response_Abstract $response)
    {
        Connection::close('');
    }
}