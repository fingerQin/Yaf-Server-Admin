<?php
/**
 * 控制器路由更改为允许驼峰命名法。
 * @author fingerQin
 * @date 2019-04-23
 */

namespace Common\plugins;

class Router extends \Yaf_Plugin_Abstract
{
    /**
     * 路由结束之后触发。
     *
     * @param \Yaf_Request_Abstract $request
     * @param \Yaf_Response_Abstract $response
     */
    public function routerShutdown(\Yaf_Request_Abstract $request, \Yaf_Response_Abstract $response)
    {
        $uri      = $request->getRequestUri();
        $uri      = trim($uri, '/');
        $uriSplit = explode('/', $uri);
        $length   = count($uriSplit);
        if ($length == 2) { // 说明是缺省的写法。只包含控制器与Action。
            $request->controller = $uriSplit[0];
        } else if ($length == 3) { // 完整写法：模块/控制器/操作。
            $request->controller = $uriSplit[1];
        }
    }
}