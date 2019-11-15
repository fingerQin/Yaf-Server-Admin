<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsModifier
 */

use finger\Utils\YUrl;

/**
 * 创建一个管理后台的URL。
 *
 * @param  string  $controllerName  控制器名称。
 * @param  string  $actionName      操作名称。
 * @param  array   $params          参数。
 * @return string
 */
function smarty_modifier_url($controllerName, $actionName = '', $params = [])
{
    return YUrl::createBackendUrl($controllerName, $actionName, $params);
}